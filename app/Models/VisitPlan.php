<?php

namespace App\Models;

use App\Enums\ActivityType;
use App\Enums\PlanStatus;
use App\Enums\PriorityLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class VisitPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plan_number',
        'customer_id',
        'area_id',
        'owner_id',
        'plan_month',
        'planned_date',
        'start_time',
        'end_time',
        'activity_type',
        'priority',
        'monthly_objective',
        'specific_objective',
        'resource_notes',
        'location_text',
        'status',
        'rescheduled_from_id',
        'cancellation_reason',
        'actual_start_at',
        'actual_end_at',
        'started_by',
        'completed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'activity_type' => ActivityType::class,
        'priority' => PriorityLevel::class,
        'status' => PlanStatus::class,
        'planned_date' => 'date',
        'actual_start_at' => 'datetime',
        'actual_end_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'visit_plan_members');
    }

    public function visitReport(): HasOne
    {
        return $this->hasOne(VisitReport::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    public function directorInputs(): HasMany
    {
        return $this->hasMany(DirectorInput::class);
    }

    public function rescheduledFrom(): BelongsTo
    {
        return $this->belongsTo(VisitPlan::class, 'rescheduled_from_id');
    }

    public function rescheduledTo(): HasMany
    {
        return $this->hasMany(VisitPlan::class, 'rescheduled_from_id');
    }

    public function starter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
