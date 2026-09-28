<?php

namespace App\Http\Controllers;

use App\Enums\ActivityType;
use App\Enums\PlanStatus;
use App\Enums\PriorityLevel;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Segment;
use App\Models\User;
use App\Models\VisitPlan;
use App\Support\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VisitPlanController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $areaId = $request->input('area_id');
        $segmentId = $request->input('segment_id');
        $priority = $request->input('priority');
        $ownerId = $request->input('owner_id');

        $query = VisitPlan::with(['customer', 'area', 'owner', 'members', 'visitReport'])
            ->where('plan_month', $month);

        if (!$user->isAdmin() && !$user->isDirector()) {
            $userAreaIds = $user->areas()->pluck('areas.id')->toArray();
            $query->where(function ($q) use ($user, $userAreaIds) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('members', fn($m) => $m->where('users.id', $user->id))
                  ->orWhereIn('area_id', $userAreaIds);
            });
        }

        if ($areaId) {
            $query->where('area_id', $areaId);
        }
        if ($segmentId) {
            $query->whereHas('customer', fn($q) => $q->where('segment_id', $segmentId));
        }
        if ($priority) {
            $query->where('priority', $priority);
        }
        if ($ownerId) {
            $query->where('owner_id', $ownerId);
        }

        $plans = $query->orderBy('planned_date')->orderBy('start_time')->get();

        // Prepare Calendar Grid Data
        $carbonMonth = Carbon::parse($month . '-01');
        $daysInMonth = $carbonMonth->daysInMonth;
        // IsoDayOfWeek: 1 (Mon) to 7 (Sun)
        $startOfWeekOffset = $carbonMonth->copy()->startOfMonth()->dayOfWeekIso - 1; // 0 = Mon, 6 = Sun

        // Group plans by date string (Y-m-d)
        $plansByDate = $plans->groupBy(function($p) {
            return $p->planned_date ? $p->planned_date->format('Y-m-d') : '';
        });

        // Master lists for filters and form
        $areas = $user->isAdmin() || $user->isDirector()
            ? Area::where('is_active', true)->get()
            : $user->areas()->where('is_active', true)->get();

        $segments = Segment::where('is_active', true)->get();

        $customers = Customer::with(['area', 'segment'])
            ->where('is_active', true)
            ->when(!$user->isAdmin() && !$user->isDirector(), function ($q) use ($user) {
                $userAreaIds = $user->areas()->pluck('areas.id')->toArray();
                $q->whereIn('area_id', $userAreaIds);
            })
            ->get();

        $teamUsers = User::where('is_active', true)->orderBy('full_name')->get();

        return view('plans.index', compact(
            'plans', 'month', 'areaId', 'segmentId', 'priority', 'ownerId',
            'areas', 'segments', 'customers', 'teamUsers',
            'carbonMonth', 'daysInMonth', 'startOfWeekOffset', 'plansByDate'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'planned_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'activity_type' => ['required', 'string'],
            'priority' => ['required', 'string'],
            'monthly_objective' => ['required', 'string', 'max:1000'],
            'specific_objective' => ['required', 'string', 'max:1000'],
            'resource_notes' => ['nullable', 'string', 'max:1000'],
            'location_text' => ['nullable', 'string', 'max:255'],
            'members' => ['nullable', 'array'],
            'members.*' => ['exists:users,id'],
        ], [
            'end_time.after' => 'Waktu selesai tidak boleh sebelum atau sama dengan waktu mulai.',
            'customer_id.required' => 'Customer wajib dipilih.',
            'planned_date.required' => 'Tanggal rencana kunjungan wajib diisi.',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);

        $plannedDate = Carbon::parse($validated['planned_date']);
        $planMonth = $plannedDate->format('Y-m');

        // Prevent double-submit by checking duplicate plan for same customer, date, start_time
        $exists = VisitPlan::where('customer_id', $customer->id)
            ->where('planned_date', $validated['planned_date'])
            ->where('start_time', $validated['start_time'])
            ->where('owner_id', Auth::id())
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['customer_id' => 'Rencana kunjungan untuk customer ini pada tanggal dan waktu yang sama sudah pernah dibuat.']);
        }

        $planNumber = 'VP-' . $plannedDate->format('Ym') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($validated, $customer, $plannedDate, $planMonth, $planNumber, &$plan) {
            $plan = VisitPlan::create([
                'plan_number' => $planNumber,
                'customer_id' => $customer->id,
                'area_id' => $customer->area_id, // Area automatically follows customer
                'owner_id' => Auth::id(),
                'plan_month' => $planMonth,
                'planned_date' => $validated['planned_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'activity_type' => $validated['activity_type'],
                'priority' => $validated['priority'],
                'monthly_objective' => $validated['monthly_objective'],
                'specific_objective' => $validated['specific_objective'],
                'resource_notes' => $validated['resource_notes'] ?? null,
                'location_text' => $validated['location_text'] ?? $customer->address,
                'status' => PlanStatus::PLANNED->value,
                'created_by' => Auth::id(),
            ]);

            if (!empty($validated['members'])) {
                $plan->members()->sync($validated['members']);
            }

            AuditLogger::log('CREATE_VISIT_PLAN', 'VISIT_PLAN', $plan->id, null, $plan->toArray());
        });

        return redirect()->route('plans.index', ['month' => $planMonth])
            ->with('success', 'Rencana kunjungan ' . $plan->plan_number . ' berhasil disimpan!');
    }

    public function show(VisitPlan $visitPlan): View
    {
        $this->authorize('view', $visitPlan);
        $user = Auth::user();
        $visitPlan->load(['customer', 'area', 'owner', 'members', 'visitReport.submitter', 'followUps', 'directorInputs.assignedUser']);

        $customers = Customer::with(['area', 'segment'])
            ->where('is_active', true)
            ->when(!$user->isAdmin() && !$user->isDirector(), function ($q) use ($user) {
                $userAreaIds = $user->areas()->pluck('areas.id')->toArray();
                $q->whereIn('area_id', $userAreaIds);
            })
            ->get();

        $teamUsers = User::where('is_active', true)->orderBy('full_name')->get();

        return view('plans.show', compact('visitPlan', 'customers', 'teamUsers'));
    }

    public function update(Request $request, VisitPlan $visitPlan): RedirectResponse
    {
        $this->authorize('update', $visitPlan);

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'planned_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'activity_type' => ['required', 'string'],
            'priority' => ['required', 'string'],
            'monthly_objective' => ['required', 'string', 'max:1000'],
            'specific_objective' => ['required', 'string', 'max:1000'],
            'resource_notes' => ['nullable', 'string', 'max:1000'],
            'location_text' => ['nullable', 'string', 'max:255'],
            'members' => ['nullable', 'array'],
            'members.*' => ['exists:users,id'],
        ], [
            'end_time.after' => 'Waktu selesai tidak boleh sebelum atau sama dengan waktu mulai.',
            'customer_id.required' => 'Customer wajib dipilih.',
            'planned_date.required' => 'Tanggal rencana kunjungan wajib diisi.',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $plannedDate = Carbon::parse($validated['planned_date']);
        $planMonth = $plannedDate->format('Y-m');

        DB::transaction(function () use ($visitPlan, $validated, $customer, $plannedDate, $planMonth) {
            $before = $visitPlan->toArray();

            $visitPlan->update([
                'customer_id' => $customer->id,
                'area_id' => $customer->area_id,
                'plan_month' => $planMonth,
                'planned_date' => $validated['planned_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'activity_type' => $validated['activity_type'],
                'priority' => $validated['priority'],
                'monthly_objective' => $validated['monthly_objective'],
                'specific_objective' => $validated['specific_objective'],
                'resource_notes' => $validated['resource_notes'] ?? null,
                'location_text' => $validated['location_text'] ?? $customer->address,
                'updated_by' => Auth::id(),
            ]);

            if (isset($validated['members'])) {
                $visitPlan->members()->sync($validated['members']);
            } else {
                $visitPlan->members()->detach();
            }

            AuditLogger::log('UPDATE_VISIT_PLAN', 'VISIT_PLAN', $visitPlan->id, $before, $visitPlan->toArray());
        });

        return back()->with('success', 'Rencana kunjungan ' . $visitPlan->plan_number . ' berhasil diperbarui!');
    }

    public function destroy(VisitPlan $visitPlan): RedirectResponse
    {
        $this->authorize('delete', $visitPlan);

        if ($visitPlan->visitReport) {
            return back()->withErrors(['visit_plan' => 'Rencana kunjungan yang sudah memiliki Laporan Kunjungan tidak dapat dihapus.']);
        }

        $planNumber = $visitPlan->plan_number;
        $planMonth = $visitPlan->plan_month;

        DB::transaction(function () use ($visitPlan) {
            $before = $visitPlan->toArray();
            $visitPlan->members()->detach();
            $visitPlan->delete();

            AuditLogger::log('DELETE_VISIT_PLAN', 'VISIT_PLAN', $visitPlan->id, $before, null);
        });

        return redirect()->route('plans.index', ['month' => $planMonth])
            ->with('success', 'Rencana kunjungan ' . $planNumber . ' berhasil dihapus.');
    }

    public function cancel(Request $request, VisitPlan $visitPlan): RedirectResponse
    {
        $this->authorize('update', $visitPlan);

        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:1000'],
        ], [
            'cancellation_reason.required' => 'Alasan pembatalan wajib diisi.',
        ]);

        $before = $visitPlan->toArray();
        $visitPlan->status = PlanStatus::CANCELLED->value;
        $visitPlan->cancellation_reason = $validated['cancellation_reason'];
        $visitPlan->updated_by = Auth::id();
        $visitPlan->save();

        AuditLogger::log('CANCEL_VISIT_PLAN', 'VISIT_PLAN', $visitPlan->id, $before, $visitPlan->toArray());

        return back()->with('success', 'Rencana kunjungan ' . $visitPlan->plan_number . ' berhasil dibatalkan.');
    }

    public function reschedule(Request $request, VisitPlan $visitPlan): RedirectResponse
    {
        $this->authorize('update', $visitPlan);

        $validated = $request->validate([
            'planned_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $plannedDate = Carbon::parse($validated['planned_date']);
        $planMonth = $plannedDate->format('Y-m');

        DB::transaction(function () use ($visitPlan, $validated, $plannedDate, $planMonth) {
            $oldStatus = $visitPlan->toArray();

            // Mark old plan as RESCHEDULED
            $visitPlan->status = PlanStatus::RESCHEDULED->value;
            $visitPlan->updated_by = Auth::id();
            $visitPlan->save();

            // Create new rescheduled plan
            $newPlanNumber = 'VP-' . $plannedDate->format('Ym') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $newPlan = VisitPlan::create([
                'plan_number' => $newPlanNumber,
                'customer_id' => $visitPlan->customer_id,
                'area_id' => $visitPlan->area_id,
                'owner_id' => $visitPlan->owner_id,
                'plan_month' => $planMonth,
                'planned_date' => $validated['planned_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'activity_type' => $visitPlan->activity_type->value ?? $visitPlan->activity_type,
                'priority' => $visitPlan->priority->value ?? $visitPlan->priority,
                'monthly_objective' => $visitPlan->monthly_objective,
                'specific_objective' => $visitPlan->specific_objective,
                'resource_notes' => $visitPlan->resource_notes,
                'location_text' => $visitPlan->location_text,
                'status' => PlanStatus::PLANNED->value,
                'rescheduled_from_id' => $visitPlan->id,
                'created_by' => Auth::id(),
            ]);

            $memberIds = $visitPlan->members()->pluck('users.id')->toArray();
            if (!empty($memberIds)) {
                $newPlan->members()->sync($memberIds);
            }

            AuditLogger::log('RESCHEDULE_VISIT_PLAN', 'VISIT_PLAN', $visitPlan->id, $oldStatus, [
                'rescheduled_to_id' => $newPlan->id,
                'new_plan_number' => $newPlanNumber
            ]);
        });

        return back()->with('success', 'Rencana kunjungan berhasil dijadwalkan ulang.');
    }
}
