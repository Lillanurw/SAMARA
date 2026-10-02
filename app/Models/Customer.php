<?php

namespace App\Models;

use App\Enums\PriorityTier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_code',
        'customer_name', 'instansi', 'satuan', 'contact_pangkat', 'contact_letting',
        'area_id',
        'segment_id',
        'owner_id',
        'address',
        'city',
        'province',
        'latitude',
        'longitude',
        'contact_name',
        'contact_position',
        'contact_phone',
        'contact_email',
        'priority_tier',
        'notes',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'priority_tier' => PriorityTier::class,
        'is_active' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function visitPlans(): HasMany
    {
        return $this->hasMany(VisitPlan::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    public function directorInputs(): HasMany
    {
        return $this->hasMany(DirectorInput::class);
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

