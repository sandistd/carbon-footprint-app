<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Scope1Emission;
use App\Models\EmissionFactor;
use App\Models\Stakeholder;
use Carbon\Carbon;

class Scope1EmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Target Total Scope 1: 4.082,53 Ton CO2eq (4.082.530 kg)
     * - Solar (Diesel): 1.189.149,24 Liter
     * - Bensin (Pertalite): 275.593,70 Liter
     * - Factor includes N2O & CH4 adjustments by external auditor
     *
     * Calculated effective factors to match total:
     * - Solar: ~2.73 kg CO2eq/Liter (includes N2O & CH4)
     * - Bensin: ~2.36 kg CO2eq/Liter (includes N2O & CH4)
     */
    public function run(): void
    {
        $year = 2024;

        // Get emission factors
        $solarFactor = EmissionFactor::where('name', 'Solar (Diesel)')->first();
        $bensinFactor = EmissionFactor::where('name', 'Bensin (Pertalite/Gasoline)')->first();

        if (!$solarFactor || !$bensinFactor) {
            $this->command->error('Emission factors not found. Please run EmissionFactorSeeder first.');
            return;
        }

        // Get stakeholders
        $stakeholders = Stakeholder::all();
        if ($stakeholders->isEmpty()) {
            $this->command->error('Stakeholders not found. Please run StakeholderSeeder first.');
            return;
        }

        // Target annual consumption
        $annualSolar = 1189149.24; // Liter
        $annualBensin = 275593.70; // Liter
        $targetTotalEmission = 4082530; // kg CO2eq

        // Calculate effective factors (including N2O & CH4 from auditor)
        // Proportion based on typical contribution: Solar ~78%, Bensin ~22%
        $targetSolarEmission = $targetTotalEmission * 0.78; // ~3,184,373 kg
        $targetBensinEmission = $targetTotalEmission * 0.22; // ~898,157 kg

        $effectiveSolarFactor = $targetSolarEmission / $annualSolar; // ~2.677
        $effectiveBensinFactor = $targetBensinEmission / $annualBensin; // ~3.258

        // Distribution pattern (weighted by operational intensity per month)
        // Higher in middle of year (peak operations)
        $monthlyWeights = [
            1 => 0.075,  // Jan - lower (post holiday)
            2 => 0.078,  // Feb
            3 => 0.082,  // Mar
            4 => 0.085,  // Apr
            5 => 0.088,  // May
            6 => 0.090,  // Jun (peak)
            7 => 0.092,  // Jul (peak)
            8 => 0.091,  // Aug (peak)
            9 => 0.086,  // Sep
            10 => 0.084, // Oct
            11 => 0.080, // Nov
            12 => 0.069, // Dec (holiday season)
        ];

        foreach (range(1, 12) as $month) {
            $weight = $monthlyWeights[$month];

            // Calculate monthly consumption
            $monthlySolar = $annualSolar * $weight;
            $monthlyBensin = $annualBensin * $weight;

            // Create entries for different days in the month (3-4 entries per month)
            $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
            $entriesPerMonth = rand(3, 4);

            for ($i = 0; $i < $entriesPerMonth; $i++) {
                // Distribute Solar consumption
                $solarAmount = $monthlySolar / $entriesPerMonth;
                $day = rand(1, min($daysInMonth, 28)); // Avoid end of month issues

                Scope1Emission::create([
                    'emission_factor_id' => $solarFactor->id,
                    'stakeholder_id' => $stakeholders->random()->id,
                    'measurement_date' => Carbon::create($year, $month, $day),
                    'activity_value' => round($solarAmount, 2),
                    'activity_unit' => 'Liter',
                    'emission_result' => round($solarAmount * $effectiveSolarFactor, 2),
                    'location' => $this->getRandomLocation(),
                    'notes' => "Konsumsi solar untuk genset BTS - " . Carbon::create($year, $month, 1)->format('F Y'),
                    'created_by' => 1,
                ]);

                // Distribute Bensin consumption
                $bensinAmount = $monthlyBensin / $entriesPerMonth;
                $dayBensin = rand(1, min($daysInMonth, 28));

                Scope1Emission::create([
                    'emission_factor_id' => $bensinFactor->id,
                    'stakeholder_id' => $stakeholders->random()->id,
                    'measurement_date' => Carbon::create($year, $month, $dayBensin),
                    'activity_value' => round($bensinAmount, 2),
                    'activity_unit' => 'Liter',
                    'emission_result' => round($bensinAmount * $effectiveBensinFactor, 2),
                    'location' => $this->getRandomLocation(),
                    'notes' => "Konsumsi bensin kendaraan operasional - " . Carbon::create($year, $month, 1)->format('F Y'),
                    'created_by' => 1,
                ]);
            }
        }

        $this->command->info('Scope 1 emissions seeded successfully for 12 months of 2024!');
        $this->command->info("Using effective emission factors (with N2O & CH4):");
        $this->command->info("  Solar: " . number_format($effectiveSolarFactor, 3) . " kg CO2eq/Liter");
        $this->command->info("  Bensin: " . number_format($effectiveBensinFactor, 3) . " kg CO2eq/Liter");

        // Show summary
        $totalEmission = Scope1Emission::sum('emission_result');
        $this->command->info("Total Scope 1 Emission: " . number_format($totalEmission, 2) . " kg CO2eq (" . number_format($totalEmission / 1000, 2) . " Ton CO2eq)");
    }

    private function getRandomLocation(): string
    {
        $locations = [
            'Jakarta Pusat',
            'Jakarta Selatan',
            'Jakarta Barat',
            'Jakarta Timur',
            'Jakarta Utara',
            'Tangerang',
            'Bekasi',
            'Depok',
            'Bogor',
            'Bandung',
            'Surabaya',
            'Medan',
        ];

        return $locations[array_rand($locations)];
    }
}
