<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Scope2Emission;
use App\Models\EmissionFactor;
use App\Models\Stakeholder;
use Carbon\Carbon;

class Scope2EmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Target Total Scope 2: 744.398,86 Ton CO2eq
     * - Konsumsi Listrik PLN: 956.319.188,94 KWh
     * - REC (Renewable Energy Certificate): 1.000.000 KWh
     * - Net Grid: 955.319.188,94 KWh
     * - Target Emission: 744.398,86 Ton CO2eq
     * - Calculated Factor: 744.398,86 / 955.319,19 MWh = 0.779 kg CO2eq/KWh
     */
    public function run(): void
    {
        $year = 2024;

        // Get emission factor
        $listrikFactor = EmissionFactor::where('name', 'Listrik PLN (Grid)')->first();

        if (!$listrikFactor) {
            $this->command->error('Listrik PLN emission factor not found. Please run EmissionFactorSeeder first.');
            return;
        }

        // Get stakeholders
        $stakeholders = Stakeholder::all();
        if ($stakeholders->isEmpty()) {
            $this->command->error('Stakeholders not found. Please run StakeholderSeeder first.');
            return;
        }

        // Target calculations to match report
        $annualListrik = 956319188.94; // KWh
        $annualREC = 1000000; // KWh
        $targetEmission = 744398860; // kg CO2eq (744.398,86 Ton)

        // Calculate required factor: 744398860 / (956319188.94 - 1000000) = 0.779215
        $effectiveFactor = 0.779215; // More precise factor

        // Track total for final adjustment
        $totalEmissionGenerated = 0;
        $allEmissions = [];

        // Distribution pattern (more consistent due to continuous BTS operations)
        $monthlyWeights = [
            1 => 0.081,  // Jan
            2 => 0.082,  // Feb
            3 => 0.083,  // Mar
            4 => 0.084,  // Apr
            5 => 0.085,  // May
            6 => 0.086,  // Jun
            7 => 0.087,  // Jul (peak)
            8 => 0.086,  // Aug (peak)
            9 => 0.085,  // Sep
            10 => 0.084, // Oct
            11 => 0.083, // Nov
            12 => 0.084, // Dec
        ];

        // Track REC distribution (split across two months)
        $recDistributed = false;

        foreach (range(1, 12) as $month) {
            $weight = $monthlyWeights[$month];

            // Calculate monthly consumption
            $monthlyListrik = $annualListrik * $weight;

            // Create entries for different sites (5-6 entries per month representing different BTS clusters)
            $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
            $entriesPerMonth = rand(5, 6);

            for ($i = 0; $i < $entriesPerMonth; $i++) {
                $listrikAmount = $monthlyListrik / $entriesPerMonth;
                $day = rand(1, min($daysInMonth, 28));

                // Assign REC to first two entries of June (symbolic for mid-year REC purchase)
                $recValue = 0;
                if ($month === 6 && $i < 2 && !$recDistributed) {
                    $recValue = $annualREC / 2;
                    if ($i === 1) {
                        $recDistributed = true;
                    }
                }

                $emissionResult = round(($listrikAmount - $recValue) * $effectiveFactor, 2);

                $emission = Scope2Emission::create([
                    'emission_factor_id' => $listrikFactor->id,
                    'stakeholder_id' => $stakeholders->random()->id,
                    'measurement_date' => Carbon::create($year, $month, $day),
                    'activity_value' => round($listrikAmount, 2),
                    'activity_unit' => 'KWh',
                    'rec_value' => $recValue,
                    'emission_result' => $emissionResult,
                    'location' => $this->getRandomRegion(),
                    'notes' => $this->getMonthlyNote($month, $recValue),
                    'created_by' => 1,
                ]);

                $allEmissions[] = $emission;
                $totalEmissionGenerated += $emissionResult;
            }
        }

        // Final adjustment to match exact target
        $difference = $targetEmission - $totalEmissionGenerated;
        if (abs($difference) > 0.01 && count($allEmissions) > 0) {
            // Distribute the difference to the last entry
            $lastEmission = $allEmissions[count($allEmissions) - 1];
            $lastEmission->emission_result = round($lastEmission->emission_result + $difference, 2);
            $lastEmission->save();
            $this->command->info("Applied final adjustment: " . number_format($difference, 2) . " kg CO2eq");
        }

        $this->command->info('Scope 2 emissions seeded successfully for 12 months of 2024!');
        $this->command->info("Using effective emission factor: " . number_format($effectiveFactor, 6) . " kg CO2eq/KWh");

        // Show summary
        $totalEmission = Scope2Emission::sum('emission_result');
        $totalREC = Scope2Emission::sum('rec_value');
        $this->command->info("Total Scope 2 Emission: " . number_format($totalEmission, 2) . " kg CO2eq (" . number_format($totalEmission / 1000, 2) . " Ton CO2eq)");
        $this->command->info("Total REC Applied: " . number_format($totalREC, 2) . " KWh");
    }

    private function getRandomRegion(): string
    {
        $regions = [
            'Jabodetabek - BTS Cluster A',
            'Jabodetabek - BTS Cluster B',
            'Jabodetabek - Kantor Pusat',
            'Jawa Barat - BTS Regional',
            'Jawa Tengah - BTS Regional',
            'Jawa Timur - BTS Regional',
            'Sumatera - BTS Regional',
            'Kalimantan - BTS Regional',
            'Sulawesi - BTS Regional',
            'Bali & Nusa Tenggara - BTS Regional',
        ];

        return $regions[array_rand($regions)];
    }

    private function getMonthlyNote(int $month, float $recValue): string
    {
        $monthName = Carbon::create(2024, $month, 1)->format('F Y');

        if ($recValue > 0) {
            return "Konsumsi listrik BTS & kantor dengan REC {$recValue} KWh - {$monthName}";
        }

        return "Konsumsi listrik PLN untuk BTS & kantor - {$monthName}";
    }
}
