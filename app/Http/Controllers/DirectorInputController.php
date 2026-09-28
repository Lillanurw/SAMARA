<?php

namespace App\Http\Controllers;

use App\Enums\DirectorInputStatus;
use App\Enums\DirectorInputType;
use App\Enums\PriorityLevel;
use App\Models\Customer;
use App\Models\DirectorInput;
use App\Models\User;
use App\Models\VisitReport;
use App\Notifications\DirectorInputCreatedNotification;
use App\Support\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DirectorInputController extends Controller
{
    // Director Review Page (ADMIN & DIRECTOR only)
    public function indexReview(Request $request): View
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isDirector()) {
            abort(403, 'Anda tidak memiliki izin untuk membuka halaman ini.');
        }

        // Reports needing attention: barriers present OR engagement < 3 OR Priority A customer
        $reportsNeedingAttention = VisitReport::with(['visitPlan.customer.area', 'visitPlan.owner', 'submitter', 'directorInputs'])
            ->where(function ($q) {
                $q->whereNotNull('barrier')
                  ->orWhere('engagement_score', '<=', 3)
                  ->orWhereHas('visitPlan.customer', fn($c) => $c->where('priority_tier', 'A'));
            })
            ->orderBy('submitted_at', 'desc')
            ->paginate(10);

        $teamUsers = User::where('is_active', true)->orderBy('full_name')->get();

        $recentInputs = DirectorInput::with(['visitReport.visitPlan.customer', 'assignedUser', 'creator'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('director.review', compact('reportsNeedingAttention', 'teamUsers', 'recentInputs'));
    }

    // Create Direction (ADMIN & DIRECTOR only)
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isDirector()) {
            abort(403, 'Anda tidak memiliki izin untuk membuat arahan Director.');
        }

        $validated = $request->validate([
            'visit_report_id' => ['nullable', 'exists:visit_reports,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'topic' => ['required', 'string', 'max:255'],
            'input_type' => ['required', 'string'],
            'direction_text' => ['required', 'string', 'max:2000'],
            'assigned_to' => ['required', 'exists:users,id'],
            'due_date' => ['required', 'date'],
            'priority' => ['required', 'string'],
        ], [
            'topic.required' => 'Topik arahan wajib diisi.',
            'direction_text.required' => 'Isi arahan Director wajib diisi.',
            'assigned_to.required' => 'Penanggung jawab (PIC) wajib dipilih.',
            'due_date.required' => 'Tenggat waktu wajib diisi.',
        ]);

        $report = null;
        if (!empty($validated['visit_report_id'])) {
            $report = VisitReport::find($validated['visit_report_id']);
        }

        $customer = null;
        if (!empty($validated['customer_id'])) {
            $customer = Customer::find($validated['customer_id']);
        } elseif ($report && $report->visitPlan) {
            $customer = $report->visitPlan->customer;
        }

        DB::transaction(function () use ($validated, $report, $customer, $user, &$direction) {
            $direction = DirectorInput::create([
                'visit_report_id' => $report?->id,
                'visit_plan_id' => $report?->visit_plan_id,
                'customer_id' => $customer?->id,
                'area_id' => $customer?->area_id,
                'topic' => $validated['topic'],
                'input_type' => $validated['input_type'],
                'direction_text' => $validated['direction_text'],
                'assigned_to' => $validated['assigned_to'],
                'priority' => $validated['priority'],
                'due_date' => $validated['due_date'],
                'status' => DirectorInputStatus::OPEN->value,
                'created_by' => $user->id,
            ]);

            AuditLogger::log('CREATE_DIRECTOR_DIRECTION', 'DIRECTOR_INPUT', $direction->id, null, $direction->toArray());

            // Notify assigned PIC user
            $assignedUser = User::find($direction->assigned_to);
            if ($assignedUser) {
                $assignedUser->notify(new DirectorInputCreatedNotification($direction));
            }

            // Notify report submitter if distinct from assigned PIC
            if ($report && $report->submitter && $report->submitter->id !== $assignedUser?->id) {
                $report->submitter->notify(new DirectorInputCreatedNotification($direction));
            }
        });

        return back()->with('success', 'Arahan Director berhasil dikirimkan!');
    }

    // "Arahan untuk Saya" Page (For TIM & all users)
    public function myDirections(Request $request): View
    {
        $user = Auth::user();
        $tab = $request->input('tab', 'baru');
        $today = Carbon::today()->format('Y-m-d');

        $query = DirectorInput::with(['visitReport.visitPlan', 'customer.area', 'creator', 'assignedUser'])
            ->when(!$user->isAdmin() && !$user->isDirector(), function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            });

        switch ($tab) {
            case 'acknowledged':
                $query->where('status', DirectorInputStatus::ACKNOWLEDGED->value);
                break;
            case 'in_progress':
                $query->where('status', DirectorInputStatus::IN_PROGRESS->value);
                break;
            case 'overdue':
                $query->whereNotIn('status', [DirectorInputStatus::CLOSED->value, DirectorInputStatus::CANCELLED->value])
                      ->where('due_date', '<', $today);
                break;
            case 'closed':
                $query->where('status', DirectorInputStatus::CLOSED->value);
                break;
            case 'baru':
            default:
                $query->where('status', DirectorInputStatus::OPEN->value);
                break;
        }

        $directions = $query->orderBy('due_date')->paginate(15);

        // Counts
        $baseQuery = DirectorInput::when(!$user->isAdmin() && !$user->isDirector(), function ($q) use ($user) {
            $q->where('assigned_to', $user->id);
        });

        $counts = [
            'baru' => (clone $baseQuery)->where('status', DirectorInputStatus::OPEN->value)->count(),
            'acknowledged' => (clone $baseQuery)->where('status', DirectorInputStatus::ACKNOWLEDGED->value)->count(),
            'in_progress' => (clone $baseQuery)->where('status', DirectorInputStatus::IN_PROGRESS->value)->count(),
            'overdue' => (clone $baseQuery)->whereNotIn('status', [DirectorInputStatus::CLOSED->value, DirectorInputStatus::CANCELLED->value])->where('due_date', '<', $today)->count(),
            'closed' => (clone $baseQuery)->where('status', DirectorInputStatus::CLOSED->value)->count(),
        ];

        return view('directions.my_directions', compact('directions', 'tab', 'counts'));
    }

    // Endpoint 1: Acknowledge (OPEN -> ACKNOWLEDGED)
    public function acknowledge(DirectorInput $directorInput): RedirectResponse
    {
        $this->authorize('acknowledge', $directorInput);

        if ($directorInput->status !== DirectorInputStatus::OPEN) {
            return back()->withErrors(['direction' => 'Hanya arahan berstatus OPEN yang dapat di-acknowledge.']);
        }

        $before = $directorInput->toArray();
        $directorInput->status = DirectorInputStatus::ACKNOWLEDGED->value;
        $directorInput->acknowledged_at = now();
        $directorInput->acknowledged_by = Auth::id();
        $directorInput->updated_by = Auth::id();
        $directorInput->save();

        AuditLogger::log('ACKNOWLEDGE_DIRECTOR_INPUT', 'DIRECTOR_INPUT', $directorInput->id, $before, $directorInput->toArray());

        return back()->with('success', 'Anda telah mengonfirmasi membaca arahan Director ini.');
    }

    // Endpoint 2: Start (ACKNOWLEDGED -> IN_PROGRESS)
    public function start(DirectorInput $directorInput): RedirectResponse
    {
        $this->authorize('start', $directorInput);

        if ($directorInput->status !== DirectorInputStatus::ACKNOWLEDGED) {
            return back()->withErrors(['direction' => 'Arahan harus di-acknowledge terlebih dahulu sebelum dimulai.']);
        }

        $before = $directorInput->toArray();
        $directorInput->status = DirectorInputStatus::IN_PROGRESS->value;
        $directorInput->started_at = now();
        $directorInput->updated_by = Auth::id();
        $directorInput->save();

        AuditLogger::log('START_DIRECTOR_INPUT', 'DIRECTOR_INPUT', $directorInput->id, $before, $directorInput->toArray());

        return back()->with('success', 'Status arahan diubah menjadi In Progress.');
    }

    // Endpoint 3: Complete (IN_PROGRESS -> CLOSED, requires completion_note)
    public function complete(Request $request, DirectorInput $directorInput): RedirectResponse
    {
        $this->authorize('complete', $directorInput);

        if ($directorInput->status !== DirectorInputStatus::IN_PROGRESS) {
            return back()->withErrors(['direction' => 'Hanya arahan berstatus IN_PROGRESS yang dapat diselesaikan.']);
        }

        $validated = $request->validate([
            'completion_note' => ['required', 'string', 'max:2000'],
        ], [
            'completion_note.required' => 'Catatan penyelesaian (completion note) wajib diisi untuk menutup arahan.',
        ]);

        $before = $directorInput->toArray();
        $directorInput->status = DirectorInputStatus::CLOSED->value;
        $directorInput->completion_note = $validated['completion_note'];
        $directorInput->closed_at = now();
        $directorInput->updated_by = Auth::id();
        $directorInput->save();

        AuditLogger::log('CLOSE_DIRECTOR_INPUT', 'DIRECTOR_INPUT', $directorInput->id, $before, $directorInput->toArray());

        return back()->with('success', 'Arahan Director berhasil diselesaikan!');
    }
}
