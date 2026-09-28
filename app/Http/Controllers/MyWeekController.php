<?php

namespace App\Http\Controllers;

use App\Enums\PlanStatus;
use App\Models\VisitPlan;
use App\Support\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MyWeekController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $startDateStr = $request->input('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $startDate = Carbon::parse($startDateStr)->startOfWeek();
        $endDate = $startDate->copy()->endOfWeek();

        $plansQuery = VisitPlan::with(['customer.area', 'customer.segment', 'area', 'owner', 'members', 'visitReport']);

        if (!$user->isAdmin() && !$user->isDirector()) {
            $userAreaIds = $user->areas()->pluck('areas.id')->toArray();
            $plansQuery->where(function ($query) use ($user, $userAreaIds) {
                $query->where('owner_id', $user->id)
                      ->orWhereHas('members', fn($q) => $q->where('users.id', $user->id))
                      ->orWhereIn('area_id', $userAreaIds);
            });
        }

        $plans = $plansQuery
            ->whereBetween('planned_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('planned_date')
            ->orderBy('start_time')
            ->get();

        // Group plans by date string (Y-m-d)
        $days = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $days[$dateStr] = [
                'date' => $date->copy(),
                'isToday' => $date->isToday(),
                'plans' => $plans->filter(fn($p) => $p->planned_date->format('Y-m-d') === $dateStr),
            ];
        }

        $customers = \App\Models\Customer::with(['area', 'segment'])
            ->where('is_active', true)
            ->when(!$user->isAdmin() && !$user->isDirector(), function ($q) use ($user) {
                $userAreaIds = $user->areas()->pluck('areas.id')->toArray();
                $q->whereIn('area_id', $userAreaIds);
            })
            ->get();

        $teamUsers = \App\Models\User::where('is_active', true)->orderBy('full_name')->get();

        return view('weekly.index', compact('days', 'startDate', 'endDate', 'customers', 'teamUsers'));
    }

    public function startVisit(VisitPlan $visitPlan): RedirectResponse
    {
        $this->authorize('startVisit', $visitPlan);

        if ($visitPlan->status === PlanStatus::COMPLETED || $visitPlan->status === PlanStatus::CANCELLED) {
            return back()->withErrors(['visit_plan' => 'Status kunjungan tidak dapat dimulai.']);
        }

        $before = $visitPlan->toArray();
        $visitPlan->status = PlanStatus::IN_PROGRESS->value;
        $visitPlan->actual_start_at = now();
        $visitPlan->started_by = Auth::id();
        $visitPlan->updated_by = Auth::id();
        $visitPlan->save();

        AuditLogger::log('START_VISIT', 'VISIT_PLAN', $visitPlan->id, $before, $visitPlan->toArray());

        return back()->with('success', 'Kunjungan ke ' . $visitPlan->customer->customer_name . ' resmi dimulai!');
    }
}
