<?php

namespace App\Models;

use App\Enums\OutcomeType;
use App\Enums\SubmitStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_number',
        'visit_plan_id',
        'actual_start_at',
        'actual_end_at',
        'outcome_summary',
        'outcome_type',
        'engagement_score',
        'attendance_summary',
        'barrier',
        'need_or_opportunity',
        'competitor_information',
        'next_step_summary',
        'follow_up_required',
        'submitted_by',
        'submitted_at',
        'submit_status',
        'correction_note',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'outcome_type' => OutcomeType::class,
        'submit_status' => SubmitStatus::class,
        'follow_up_required' => 'boolean',
        'actual_start_at' => 'datetime',
        'actual_end_at' => 'datetime',
        'submitted_at' => 'datetime',
        'engagement_score' => 'integer',
    ];

    public function visitPlan(): BelongsTo
    {
        return $this->belongsTo(VisitPlan::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    public function directorInputs(): HasMany
    {
        return $this->hasMany(DirectorInput::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
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
