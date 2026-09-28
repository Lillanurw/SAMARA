<?php

namespace App\Models;

use App\Enums\DirectorInputStatus;
use App\Enums\DirectorInputType;
use App\Enums\PriorityLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DirectorInput extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'visit_report_id',
        'visit_plan_id',
        'customer_id',
        'area_id',
        'topic',
        'input_type',
        'direction_text',
        'assigned_to',
        'priority',
        'due_date',
        'status',
        'acknowledged_at',
        'acknowledged_by',
        'started_at',
        'completion_note',
        'closed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'input_type' => DirectorInputType::class,
        'priority' => PriorityLevel::class,
        'status' => DirectorInputStatus::class,
        'due_date' => 'date',
        'acknowledged_at' => 'datetime',
        'started_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function visitReport(): BelongsTo
    {
        return $this->belongsTo(VisitReport::class);
    }

    public function visitPlan(): BelongsTo
    {
        return $this->belongsTo(VisitPlan::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function acknowledgedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
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
        if ($this->status === DirectorInputStatus::CLOSED || $this->status === DirectorInputStatus::CANCELLED) {
            return false;
        }
        return $this->due_date && $this->due_date->isPast();
    }
}
