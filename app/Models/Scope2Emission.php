<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scope2Emission extends Model
{
    protected $table = 'scope_2_emissions';

    protected $fillable = [
        'emission_factor_id',
        'stakeholder_id',
        'measurement_date',
        'activity_value',
        'activity_unit',
        'rec_value',
        'emission_result',
        'location',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'activity_value' => 'decimal:2',
        'rec_value' => 'decimal:2',
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
     * Calculate emission result based on activity value, REC, and emission factor
     * Formula: (Total KWh - REC) * Factor (kg CO2eq/KWh) / 1000 = Ton CO2eq
     */
    public function calculateEmission(): float
    {
        if (!$this->emissionFactor) {
            return 0;
        }

        $netActivity = $this->activity_value - $this->rec_value;
        return ($netActivity * $this->emissionFactor->factor) / 1000;
    }
}
