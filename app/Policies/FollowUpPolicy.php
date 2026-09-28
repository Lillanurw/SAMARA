<?php

namespace App\Policies;

use App\Models\FollowUp;
use App\Models\User;

class FollowUpPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FollowUp $followUp): bool
    {
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }

        if ($followUp->owner_id === $user->id) {
            return true;
        }

        $userAreaIds = $user->areas()->pluck('areas.id')->toArray();
        return $followUp->customer && in_array($followUp->customer->area_id, $userAreaIds);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FollowUp $followUp): bool
    {
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }

        return $followUp->owner_id === $user->id;
    }

    public function delete(User $user, FollowUp $followUp): bool
    {
        return $user->isAdmin() || $followUp->owner_id === $user->id;
    }
}
