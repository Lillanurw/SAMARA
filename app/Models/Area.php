<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'area_code',
        'area_name',
        'region',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_areas');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function visitPlans(): HasMany
    {
        return $this->hasMany(VisitPlan::class);
    }

    public function directorInputs(): HasMany
    {
        return $this->hasMany(DirectorInput::class);
    }
}
