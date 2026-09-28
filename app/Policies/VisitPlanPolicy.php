<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VisitPlan;

class VisitPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, VisitPlan $visitPlan): bool
    {
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }

        // Tim can view if owner, member, or if area matches user assigned areas
        if ($visitPlan->owner_id === $user->id) {
            return true;
        }

        if ($visitPlan->members->contains($user->id)) {
            return true;
        }

        $userAreaIds = $user->areas()->pluck('areas.id')->toArray();
        return in_array($visitPlan->area_id, $userAreaIds);
    }

    public function create(User $user): bool
    {
        return true; // Admin, Director, Tim can create visit plans
    }

    public function update(User $user, VisitPlan $visitPlan): bool
    {
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }

        return $visitPlan->owner_id === $user->id;
    }

    public function delete(User $user, VisitPlan $visitPlan): bool
    {
        return $user->isAdmin() || $visitPlan->owner_id === $user->id;
    }

    public function startVisit(User $user, VisitPlan $visitPlan): bool
    {
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }

        return $visitPlan->owner_id === $user->id || $visitPlan->members->contains($user->id);
    }
}
