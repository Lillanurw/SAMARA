<?php

namespace App\Http\Controllers;

use App\Enums\DirectorInputStatus;
use App\Enums\FollowUpStatus;
use App\Enums\PlanStatus;
use App\Models\DirectorInput;
use App\Models\FollowUp;
use App\Models\VisitPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $today = Carbon::today();

        // 1. Agenda Hari Ini
        $todayAgendaQuery = VisitPlan::with(['customer', 'area', 'members']);
        if (!$user->isAdmin() && !$user->isDirector()) {
            $userAreaIds = $user->areas()->pluck('areas.id')->toArray();
            $todayAgendaQuery->where(function ($q) use ($user, $userAreaIds) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('members', fn($m) => $m->where('users.id', $user->id))
                  ->orWhereIn('area_id', $userAreaIds);
            });
        }
        $todayAgenda = $todayAgendaQuery
            ->whereDate('planned_date', $today->format('Y-m-d'))
            ->orderBy('start_time')
            ->get();

        // 2. Laporan Tertunda (Visits in past or in_progress that need a report)
        $pendingReportQuery = VisitPlan::with(['customer']);
        if (!$user->isAdmin() && !$user->isDirector()) {
            $userAreaIds = $user->areas()->pluck('areas.id')->toArray();
            $pendingReportQuery->where(function ($q) use ($user, $userAreaIds) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('members', fn($m) => $m->where('users.id', $user->id))
                  ->orWhereIn('area_id', $userAreaIds);
            });
        }
        $pendingReportPlans = $pendingReportQuery
            ->whereIn('status', [PlanStatus::IN_PROGRESS, PlanStatus::PLANNED])
            ->where('planned_date', '<=', $today->format('Y-m-d'))
            ->doesntHave('visitReport')
            ->orderBy('planned_date', 'desc')
            ->take(5)
            ->get();

        // 3. Overdue Follow-ups
        $overdueFollowUps = FollowUp::with(['customer'])
            ->when(!$user->isAdmin() && !$user->isDirector(), fn($q) => $q->where('owner_id', $user->id))
            ->whereNotIn('status', [FollowUpStatus::DONE->value, FollowUpStatus::CANCELLED->value])
            ->where('due_date', '<', $today->format('Y-m-d'))
            ->orderBy('due_date')
            ->take(5)
            ->get();

        // 4. Arahan Director untuk User (Latest 5 & Counts)
        $directorQuery = DirectorInput::with(['customer', 'area', 'creator'])
            ->when(!$user->isAdmin() && !$user->isDirector(), function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            });

        $recentDirections = (clone $directorQuery)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $directionCounts = [
            'open' => (clone $directorQuery)->where('status', DirectorInputStatus::OPEN)->count(),
            'acknowledged' => (clone $directorQuery)->where('status', DirectorInputStatus::ACKNOWLEDGED)->count(),
            'in_progress' => (clone $directorQuery)->where('status', DirectorInputStatus::IN_PROGRESS)->count(),
            'overdue' => (clone $directorQuery)->whereNotIn('status', [DirectorInputStatus::CLOSED->value, DirectorInputStatus::CANCELLED->value])
                ->where('due_date', '<', $today->format('Y-m-d'))->count(),
        ];

        // 5. Agenda Terdekat (Next 7 days excluding today)
        $upcomingQuery = VisitPlan::with(['customer', 'area']);
        if (!$user->isAdmin() && !$user->isDirector()) {
            $userAreaIds = $user->areas()->pluck('areas.id')->toArray();
            $upcomingQuery->where(function ($q) use ($user, $userAreaIds) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('members', fn($m) => $m->where('users.id', $user->id))
                  ->orWhereIn('area_id', $userAreaIds);
            });
        }
        $upcomingAgenda = $upcomingQuery
            ->whereDate('planned_date', '>', $today->format('Y-m-d'))
            ->orderBy('planned_date')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        // 6. Action Saya (Open & In Progress Follow-ups)
        $myActions = FollowUp::with(['customer'])
            ->when(!$user->isAdmin() && !$user->isDirector(), fn($q) => $q->where('owner_id', $user->id))
            ->whereIn('status', [FollowUpStatus::OPEN->value, FollowUpStatus::IN_PROGRESS->value, FollowUpStatus::BLOCKED->value])
            ->orderBy('due_date')
            ->take(5)
            ->get();

        return view('home.index', compact(
            'todayAgenda',
            'pendingReportPlans',
            'overdueFollowUps',
            'recentDirections',
            'directionCounts',
            'upcomingAgenda',
            'myActions'
        ));
    }
}
