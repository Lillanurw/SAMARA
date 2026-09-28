<?php

namespace App\Models;

use App\Enums\FollowUpStatus;
use App\Enums\PriorityLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowUp extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'action_number',
        'visit_plan_id',
        'visit_report_id',
        'customer_id',
        'action_title',
        'action_detail',
        'owner_id',
        'due_date',
        'priority',
        'status',
        'completion_note',
        'completed_at',
        'blocked_reason',
        'rescheduled_to',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'priority' => PriorityLevel::class,
        'status' => FollowUpStatus::class,
        'due_date' => 'date',
        'rescheduled_to' => 'date',
        'completed_at' => 'datetime',
    ];

    public function visitPlan(): BelongsTo
    {
        return $this->belongsTo(VisitPlan::class);
    }

    public function visitReport(): BelongsTo
    {
        return $this->belongsTo(VisitReport::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isOverdue(): bool
    {
        if (in_array($this->status, [FollowUpStatus::DONE, FollowUpStatus::CANCELLED])) {
            return false;
        }
        return $this->due_date && $this->due_date->isPast();
    }
}
