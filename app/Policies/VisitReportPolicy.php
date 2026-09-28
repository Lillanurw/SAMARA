<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VisitReport;

class VisitReportPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, VisitReport $visitReport): bool
    {
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }

        if ($visitReport->submitted_by === $user->id) {
            return true;
        }

        $plan = $visitReport->visitPlan;
        if ($plan && ($plan->owner_id === $user->id || $plan->members->contains($user->id))) {
            return true;
        }

        $userAreaIds = $user->areas()->pluck('areas.id')->toArray();
        return $plan && in_array($plan->area_id, $userAreaIds);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, VisitReport $visitReport): bool
    {
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }

        return $visitReport->submitted_by === $user->id;
    }
}
