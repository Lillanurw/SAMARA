<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Customer $customer): bool
    {
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }

        if ($customer->owner_id === $user->id) {
            return true;
        }

        $userAreaIds = $user->areas()->pluck('areas.id')->toArray();
        return in_array($customer->area_id, $userAreaIds);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->isAdmin();
    }
}
