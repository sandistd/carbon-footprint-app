<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stakeholder extends Model
{
    protected $table = 'stakeholders';

    protected $fillable = [
        'name',
        'email',
        'position',
        'department',
        'receive_alerts',
    ];

    protected $casts = [
        'receive_alerts' => 'boolean',
    ];

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
