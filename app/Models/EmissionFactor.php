<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class EmissionFactor extends Model
{
    protected $table = 'emission_factors';

    protected $fillable = [
        'name',
        'scope',
        'category',
        'factor',
        'unit',
        'description',
        'source',
        'is_active',
    ];

    protected $casts = [
        'factor' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByScope(Builder $query, string $scope): Builder
    {
        return $query->where('scope', $scope);
    }

    public function scope1Emissions(): HasMany
    {
        return $this->hasMany(Scope1Emission::class);
    }

    public function scope2Emissions(): HasMany
    {
        return $this->hasMany(Scope2Emission::class);
    }

    public function scope3Emissions(): HasMany
    {
        return $this->hasMany(Scope3Emission::class);
    }
}
