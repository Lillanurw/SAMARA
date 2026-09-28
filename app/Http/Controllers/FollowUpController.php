<?php

namespace App\Http\Controllers;

use App\Enums\FollowUpStatus;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\User;
use App\Support\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FollowUpController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $tab = $request->input('tab', 'open');
        $today = Carbon::today()->format('Y-m-d');

        $query = FollowUp::with(['customer.area', 'visitPlan', 'visitReport', 'owner', 'creator'])
            ->when(!$user->isAdmin() && !$user->isDirector(), function ($q) use ($user) {
                $userAreaIds = $user->areas()->pluck('areas.id')->toArray();
                $q->where('owner_id', $user->id)
                  ->orWhereHas('customer', fn($c) => $c->whereIn('area_id', $userAreaIds));
            });

        switch ($tab) {
            case 'overdue':
                $query->whereNotIn('status', [FollowUpStatus::DONE->value, FollowUpStatus::CANCELLED->value])
                      ->where('due_date', '<', $today);
                break;
            case 'blocked':
                $query->where('status', FollowUpStatus::BLOCKED->value);
                break;
            case 'done':
                $query->where('status', FollowUpStatus::DONE->value);
                break;
            case 'open':
            default:
                $query->whereIn('status', [FollowUpStatus::OPEN->value, FollowUpStatus::IN_PROGRESS->value]);
                break;
        }

        $followUps = $query->orderBy('due_date')->paginate(15);

        // Counts for tabs
        $baseQuery = FollowUp::when(!$user->isAdmin() && !$user->isDirector(), function ($q) use ($user) {
            $userAreaIds = $user->areas()->pluck('areas.id')->toArray();
            $q->where('owner_id', $user->id)
              ->orWhereHas('customer', fn($c) => $c->whereIn('area_id', $userAreaIds));
        });

        $counts = [
            'open' => (clone $baseQuery)->whereIn('status', [FollowUpStatus::OPEN->value, FollowUpStatus::IN_PROGRESS->value])->count(),
            'overdue' => (clone $baseQuery)->whereNotIn('status', [FollowUpStatus::DONE->value, FollowUpStatus::CANCELLED->value])->where('due_date', '<', $today)->count(),
            'blocked' => (clone $baseQuery)->where('status', FollowUpStatus::BLOCKED->value)->count(),
            'done' => (clone $baseQuery)->where('status', FollowUpStatus::DONE->value)->count(),
        ];

        return view('followups.index', compact('followUps', 'tab', 'counts'));
    }

    public function updateStatus(Request $request, FollowUp $followUp): RedirectResponse
    {
        $this->authorize('update', $followUp);

        $validated = $request->validate([
            'status' => ['required', 'string'],
            'completion_note' => ['required_if:status,DONE', 'nullable', 'string', 'max:2000'],
            'blocked_reason' => ['required_if:status,BLOCKED', 'nullable', 'string', 'max:2000'],
            'rescheduled_to' => ['nullable', 'date'],
        ], [
            'completion_note.required_if' => 'Catatan penyelesaian (completion note) wajib diisi jika status diubah menjadi DONE.',
            'blocked_reason.required_if' => 'Alasan hambatan (blocked reason) wajib diisi jika status diubah menjadi BLOCKED.',
        ]);

        $before = $followUp->toArray();

        $followUp->status = $validated['status'];
        if ($validated['status'] === FollowUpStatus::DONE->value) {
            $followUp->completion_note = $validated['completion_note'];
            $followUp->completed_at = now();
        } elseif ($validated['status'] === FollowUpStatus::BLOCKED->value) {
            $followUp->blocked_reason = $validated['blocked_reason'];
        }

        if (!empty($validated['rescheduled_to'])) {
            $followUp->rescheduled_to = $validated['rescheduled_to'];
            $followUp->due_date = $validated['rescheduled_to'];
        }

        $followUp->updated_by = Auth::id();
        $followUp->save();

        AuditLogger::log('UPDATE_FOLLOW_UP_STATUS', 'FOLLOW_UP', $followUp->id, $before, $followUp->toArray());

        return back()->with('success', 'Status follow-up ' . $followUp->action_number . ' berhasil diperbarui.');
    }
}
