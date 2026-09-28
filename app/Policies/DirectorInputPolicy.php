<?php

namespace App\Policies;

use App\Models\DirectorInput;
use App\Models\User;

class DirectorInputPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DirectorInput $directorInput): bool
    {
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }

        // Tim can only view if assigned to them or if they own the related visit report/plan/customer
        return $directorInput->assigned_to === $user->id
            || ($directorInput->visitReport && $directorInput->visitReport->submitted_by === $user->id)
            || ($directorInput->visitPlan && $directorInput->visitPlan->owner_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isDirector();
    }

    public function update(User $user, DirectorInput $directorInput): bool
    {
        return $user->isAdmin() || $user->isDirector();
    }

    public function delete(User $user, DirectorInput $directorInput): bool
    {
        return $user->isAdmin() || $user->isDirector();
    }

    public function acknowledge(User $user, DirectorInput $directorInput): bool
    {
        return $directorInput->assigned_to === $user->id || $user->isAdmin() || $user->isDirector();
    }

    public function start(User $user, DirectorInput $directorInput): bool
    {
        return $directorInput->assigned_to === $user->id || $user->isAdmin() || $user->isDirector();
    }

    public function complete(User $user, DirectorInput $directorInput): bool
    {
        return $directorInput->assigned_to === $user->id || $user->isAdmin() || $user->isDirector();
    }
}
