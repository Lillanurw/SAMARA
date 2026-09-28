<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'full_name',
        'role',
        'manager_email',
        'avatar_url',
        'profile_picture',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function setRoleAttribute($value): void
    {
        if ($value instanceof UserRole) {
            $this->attributes['role'] = $value->value;
            return;
        }

        $normalized = strtoupper(trim((string)$value));
        $mappedRole = match ($normalized) {
            'PLANNER', 'FIELD', 'MANAGER', 'VIEWER', 'TIM' => UserRole::TIM->value,
            'ADMIN' => UserRole::ADMIN->value,
            'DIRECTOR' => UserRole::DIRECTOR->value,
            default => UserRole::TIM->value,
        };

        $this->attributes['role'] = $mappedRole;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isTim(): bool
    {
        return $this->role === UserRole::TIM;
    }

    public function isDirector(): bool
    {
        return $this->role === UserRole::DIRECTOR;
    }

    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'user_areas');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'owner_id');
    }

    public function visitPlans(): HasMany
    {
        return $this->hasMany(VisitPlan::class, 'owner_id');
    }

    public function memberVisitPlans(): BelongsToMany
    {
        return $this->belongsToMany(VisitPlan::class, 'visit_plan_members');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'owner_id');
    }

    public function assignedDirections(): HasMany
    {
        return $this->hasMany(DirectorInput::class, 'assigned_to');
    }

    public function createdDirections(): HasMany
    {
        return $this->hasMany(DirectorInput::class, 'created_by');
    }
}
