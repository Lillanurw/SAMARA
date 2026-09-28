<?php

namespace App\Http\Controllers;

use App\Enums\DirectorInputStatus;
use App\Enums\FollowUpStatus;
use App\Enums\PlanStatus;
use App\Models\Area;
use App\Models\Customer;
use App\Models\DirectorInput;
use App\Models\FollowUp;
use App\Models\Segment;
use App\Models\User;
use App\Models\VisitPlan;
use App\Models\VisitReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $type = $request->input('type', 'personal'); // 'personal' or 'overall'
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $areaId = $request->input('area_id');
        $segmentId = $request->input('segment_id');
        $ownerId = $request->input('owner_id');

        $userAreaIds = $user->areas()->pluck('areas.id')->toArray();

        // 1. Base Visit Plans Query
        $plansQuery = VisitPlan::where('plan_month', $month);

        if ($type === 'personal' && !$user->isAdmin()) {
            $plansQuery->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('members', fn($m) => $m->where('users.id', $user->id));
            });
        } elseif (!$user->isAdmin() && !$user->isDirector()) {
            // Overall dashboard for Tim still restricts to user areas
            $plansQuery->whereIn('area_id', $userAreaIds);
        }

        if ($areaId) {
            $plansQuery->where('area_id', $areaId);
        }
        if ($segmentId) {
            $plansQuery->whereHas('customer', fn($q) => $q->where('segment_id', $segmentId));
        }
        if ($ownerId) {
            $plansQuery->where('owner_id', $ownerId);
        }

        $plannedVisitsCount = (clone $plansQuery)->count();
        $completedVisitsCount = (clone $plansQuery)->where('status', PlanStatus::COMPLETED->value)->count();

        $completionRate = $plannedVisitsCount > 0 ? round(($completedVisitsCount / $plannedVisitsCount) * 100, 1) : 0;

        // Reports stats
        $reportsQuery = VisitReport::whereHas('visitPlan', function ($q) use ($month, $type, $user, $userAreaIds, $areaId, $segmentId, $ownerId) {
            $q->where('plan_month', $month);
            if ($type === 'personal' && !$user->isAdmin()) {
                $q->where('owner_id', $user->id);
            } elseif (!$user->isAdmin() && !$user->isDirector()) {
                $q->whereIn('area_id', $userAreaIds);
            }
            if ($areaId) $q->where('area_id', $areaId);
            if ($segmentId) $q->whereHas('customer', fn($cs) => $cs->where('segment_id', $segmentId));
            if ($ownerId) $q->where('owner_id', $ownerId);
        });

        $avgEngagement = round((clone $reportsQuery)->avg('engagement_score') ?? 0, 1);

        // Customer Coverage
        $visitedCustomerIds = (clone $plansQuery)->where('status', PlanStatus::COMPLETED->value)
            ->distinct()->pluck('customer_id');

        $totalCustomersCount = Customer::where('is_active', true)
            ->when(!$user->isAdmin() && !$user->isDirector(), fn($q) => $q->whereIn('area_id', $userAreaIds))
            ->when($areaId, fn($q) => $q->where('area_id', $areaId))
            ->count();

        $customerCoveragePct = $totalCustomersCount > 0 ? round(($visitedCustomerIds->count() / $totalCustomersCount) * 100, 1) : 0;

        // Overdue Follow-ups
        $overdueFollowUpsCount = FollowUp::whereNotIn('status', [FollowUpStatus::DONE->value, FollowUpStatus::CANCELLED->value])
            ->where('due_date', '<', Carbon::today()->format('Y-m-d'))
            ->when($type === 'personal', fn($q) => $q->where('owner_id', $user->id))
            ->when(!$user->isAdmin() && !$user->isDirector() && $type === 'overall', fn($q) => $q->whereHas('customer', fn($c) => $c->whereIn('area_id', $userAreaIds)))
            ->count();

        // Chart Data 1: Planned vs Completed Status Distribution
        $statusCounts = (clone $plansQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Chart Data 2: Activities per Area
        $areaActivityData = (clone $plansQuery)
            ->join('areas', 'visit_plans.area_id', '=', 'areas.id')
            ->select('areas.area_name', DB::raw('count(*) as count'))
            ->groupBy('areas.area_name')
            ->pluck('count', 'areas.area_name')
            ->toArray();

        // Chart Data 3: Outcome Distribution
        $outcomeData = (clone $reportsQuery)
            ->select('outcome_type', DB::raw('count(*) as count'))
            ->groupBy('outcome_type')
            ->pluck('count', 'outcome_type')
            ->toArray();

        // Chart Data 4: Direction Statistics
        $directionQuery = DirectorInput::when($type === 'personal', fn($q) => $q->where('assigned_to', $user->id))
            ->when(!$user->isAdmin() && !$user->isDirector() && $type === 'overall', fn($q) => $q->whereIn('area_id', $userAreaIds));

        $directionStats = [
            'open' => (clone $directionQuery)->where('status', DirectorInputStatus::OPEN)->count(),
            'acknowledged' => (clone $directionQuery)->where('status', DirectorInputStatus::ACKNOWLEDGED)->count(),
            'in_progress' => (clone $directionQuery)->where('status', DirectorInputStatus::IN_PROGRESS)->count(),
            'overdue' => (clone $directionQuery)->whereNotIn('status', [DirectorInputStatus::CLOSED->value, DirectorInputStatus::CANCELLED->value])->where('due_date', '<', Carbon::today()->format('Y-m-d'))->count(),
            'closed' => (clone $directionQuery)->where('status', DirectorInputStatus::CLOSED)->count(),
        ];

        // Filters dropdown options
        $areas = $user->isAdmin() || $user->isDirector()
            ? Area::where('is_active', true)->get()
            : $user->areas()->where('is_active', true)->get();

        $segments = Segment::where('is_active', true)->get();
        $teamUsers = User::where('is_active', true)->orderBy('full_name')->get();

        return view('dashboard.index', compact(
            'type',
            'month',
            'areaId',
            'segmentId',
            'ownerId',
            'plannedVisitsCount',
            'completedVisitsCount',
            'completionRate',
            'avgEngagement',
            'totalCustomersCount',
            'customerCoveragePct',
            'overdueFollowUpsCount',
            'statusCounts',
            'areaActivityData',
            'outcomeData',
            'directionStats',
            'areas',
            'segments',
            'teamUsers'
        ));
    }
}
