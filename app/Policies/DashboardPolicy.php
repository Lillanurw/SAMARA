<?php

namespace App\Policies;

use App\Models\User;

class DashboardPolicy
{
    public function view(User $user): bool
    {
        return true;
    }

    public function viewOverall(User $user): bool
    {
        return true; // overall dashboard still enforces area-based scope for TIM
    }
}
