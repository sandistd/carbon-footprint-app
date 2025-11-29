<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scope3Emission extends Model
{
    protected $table = 'scope_3_emissions';

    protected $fillable = [
        'emission_factor_id',
        'stakeholder_id',
        'category',
        'measurement_date',
        'activity_value',
        'activity_unit',
        'emission_result',
        'location',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'activity_value' => 'decimal:2',
        'emission_result' => 'decimal:2',
    ];

    public function emissionFactor(): BelongsTo
    {
        return $this->belongsTo(EmissionFactor::class);
    }

    public function stakeholder(): BelongsTo
    {
        return $this->belongsTo(Stakeholder::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Calculate emission result based on activity value and emission factor
     */
    public function calculateEmission(): float
    {
        if (!$this->emissionFactor) {
            return 0;
        }

        // Activity value * Factor / 1000 = Ton CO2eq
        return ($this->activity_value * $this->emissionFactor->factor) / 1000;
    }
}
