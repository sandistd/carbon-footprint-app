<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Scope1Emission;
use App\Models\Scope2Emission;
use App\Models\Scope3Emission;
use App\Models\EmissionFactor;
use App\Models\Stakeholder;
use Carbon\Carbon;

class EmissionSeeder2025 extends Seeder
{
    /**
     * Seed emissions data from January 1, 2025 to current date.
     *
     * This seeder creates realistic emission data showing reduction trend
     * towards 2030 target (45% reduction from 2024 baseline).
     *
     * 2024 Baseline: 752,733.86 Ton CO2eq
     * 2025 Target (linear): ~696,195 Ton CO2eq (7.5% reduction)
     */
    public function run(): void
    {
        $startDate = Carbon::create(2025, 1, 1);
        $endDate = Carbon::today(); // Dynamic: today's date

        if ($endDate->lessThan($startDate)) {
            $this->command->warn('End date is before start date. No data will be seeded.');
            return;
        }

        $this->command->info("Seeding emissions data from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

        // Get emission factors
        $solarFactor = EmissionFactor::where('name', 'Solar (Diesel)')->first();
        $bensinFactor = EmissionFactor::where('name', 'Bensin (Pertalite/Gasoline)')->first();
        $listrikFactor = EmissionFactor::where('name', 'Listrik PLN (Grid)')->first();

        if (!$solarFactor || !$bensinFactor || !$listrikFactor) {
            $this->command->error('Required emission factors not found. Please run EmissionFactorSeeder first.');
            return;
        }

        // Get stakeholders
        $stakeholders = Stakeholder::all();
        if ($stakeholders->isEmpty()) {
            $this->command->error('Stakeholders not found. Please run StakeholderSeeder first.');
            return;
        }

        // 2024 baseline values
        $baseline2024 = [
            'scope1_solar' => 1189149.24,      // Liter
            'scope1_bensin' => 275593.70,      // Liter
            'scope2_listrik' => 956319188.94,  // KWh
            'scope2_rec' => 1000000,           // KWh
            'scope3_total' => 4252470,         // kg CO2eq
        ];

        // 2025 target: 7.5% reduction from 2024
        $reductionFactor = 0.925; // 92.5% of 2024 (7.5% reduction)

        $target2025 = [
            'scope1_solar' => $baseline2024['scope1_solar'] * $reductionFactor,
            'scope1_bensin' => $baseline2024['scope1_bensin'] * $reductionFactor,
            'scope2_listrik' => $baseline2024['scope2_listrik'] * $reductionFactor,
            'scope2_rec' => 1500000, // Increased REC purchase
            'scope3_total' => $baseline2024['scope3_total'] * $reductionFactor,
        ];

        // Calculate daily averages for the period
        $daysInPeriod = $startDate->diffInDays($endDate) + 1;
        $daysInYear = 365;
        $periodRatio = $daysInPeriod / $daysInYear;

        // Seed Scope 1
        $this->seedScope1($startDate, $endDate, $target2025, $periodRatio, $solarFactor, $bensinFactor, $stakeholders);

        // Seed Scope 2
        $this->seedScope2($startDate, $endDate, $target2025, $periodRatio, $listrikFactor, $stakeholders);

        // Seed Scope 3
        $this->seedScope3($startDate, $endDate, $target2025, $periodRatio, $stakeholders);

        // Summary
        $this->showSummary($startDate, $endDate);
    }

    private function seedScope1($startDate, $endDate, $target2025, $periodRatio, $solarFactor, $bensinFactor, $stakeholders): void
    {
        $this->command->info('Seeding Scope 1 emissions...');

        $periodSolar = $target2025['scope1_solar'] * $periodRatio;
        $periodBensin = $target2025['scope1_bensin'] * $periodRatio;

        // Effective factors (including N2O & CH4)
        $effectiveSolarFactor = 2.677;
        $effectiveBensinFactor = 3.258;

        // Calculate total months in period
        $totalMonths = $startDate->copy()->diffInMonths($endDate) + 1;

        $currentDate = $startDate->copy();
        $entriesCreated = 0;

        while ($currentDate->lessThanOrEqualTo($endDate)) {
            $month = $currentDate->month;
            $year = $currentDate->year;

            // Generate 3-4 entries per month
            $entriesPerMonth = rand(3, 4);
            $daysInMonth = $currentDate->daysInMonth;

            for ($i = 0; $i < $entriesPerMonth; $i++) {
                $day = rand(1, min($daysInMonth, 28));
                $entryDate = Carbon::create($year, $month, $day);

                // Skip if entry date is beyond end date
                if ($entryDate->greaterThan($endDate)) {
                    continue;
                }

                // Solar entry
                $solarAmount = ($periodSolar / $totalMonths) / $entriesPerMonth;

                Scope1Emission::create([
                    'emission_factor_id' => $solarFactor->id,
                    'stakeholder_id' => $stakeholders->random()->id,
                    'measurement_date' => $entryDate,
                    'activity_value' => round($solarAmount, 2),
                    'activity_unit' => 'Liter',
                    'emission_result' => round($solarAmount * $effectiveSolarFactor, 2),
                    'location' => $this->getRandomLocation(),
                    'notes' => "Konsumsi solar genset BTS - " . $entryDate->format('F Y'),
                    'created_by' => 1,
                ]);

                // Bensin entry
                $bensinAmount = ($periodBensin / $totalMonths) / $entriesPerMonth;

                Scope1Emission::create([
                    'emission_factor_id' => $bensinFactor->id,
                    'stakeholder_id' => $stakeholders->random()->id,
                    'measurement_date' => $entryDate,
                    'activity_value' => round($bensinAmount, 2),
                    'activity_unit' => 'Liter',
                    'emission_result' => round($bensinAmount * $effectiveBensinFactor, 2),
                    'location' => $this->getRandomLocation(),
                    'notes' => "Konsumsi bensin kendaraan operasional - " . $entryDate->format('F Y'),
                    'created_by' => 1,
                ]);

                $entriesCreated += 2;
            }

            // Move to next month
            $currentDate->addMonth()->startOfMonth();
        }

        $this->command->info("  Created {$entriesCreated} Scope 1 entries");
    }

    private function seedScope2($startDate, $endDate, $target2025, $periodRatio, $listrikFactor, $stakeholders): void
    {
        $this->command->info('Seeding Scope 2 emissions...');

        $periodListrik = $target2025['scope2_listrik'] * $periodRatio;
        $periodREC = $target2025['scope2_rec'] * $periodRatio;
        $effectiveFactor = 0.779215;

        // Calculate total months in period
        $totalMonths = $startDate->copy()->diffInMonths($endDate) + 1;

        $currentDate = $startDate->copy();
        $entriesCreated = 0;
        $recDistributed = 0;

        while ($currentDate->lessThanOrEqualTo($endDate)) {
            $month = $currentDate->month;
            $year = $currentDate->year;

            // Generate 5-6 entries per month
            $entriesPerMonth = rand(5, 6);
            $daysInMonth = $currentDate->daysInMonth;

            for ($i = 0; $i < $entriesPerMonth; $i++) {
                $day = rand(1, min($daysInMonth, 28));
                $entryDate = Carbon::create($year, $month, $day);

                // Skip if entry date is beyond end date
                if ($entryDate->greaterThan($endDate)) {
                    continue;
                }

                $listrikAmount = ($periodListrik / $totalMonths) / $entriesPerMonth;

                // Distribute REC in mid-year months (if we've reached them)
                $recValue = 0;
                if ($month >= 5 && $month <= 7 && $recDistributed < $periodREC) {
                    $recValue = min($periodREC / 6, $periodREC - $recDistributed); // Distribute over 6 entries
                    $recDistributed += $recValue;
                }

                Scope2Emission::create([
                    'emission_factor_id' => $listrikFactor->id,
                    'stakeholder_id' => $stakeholders->random()->id,
                    'measurement_date' => $entryDate,
                    'activity_value' => round($listrikAmount, 2),
                    'activity_unit' => 'KWh',
                    'rec_value' => round($recValue, 2),
                    'emission_result' => round(($listrikAmount - $recValue) * $effectiveFactor, 2),
                    'location' => $this->getRandomRegion(),
                    'notes' => $recValue > 0
                        ? "Konsumsi listrik BTS dengan REC - " . $entryDate->format('F Y')
                        : "Konsumsi listrik PLN BTS & kantor - " . $entryDate->format('F Y'),
                    'created_by' => 1,
                ]);

                $entriesCreated++;
            }

            // Move to next month
            $currentDate->addMonth()->startOfMonth();
        }

        $this->command->info("  Created {$entriesCreated} Scope 2 entries");
    }

    private function seedScope3($startDate, $endDate, $target2025, $periodRatio, $stakeholders): void
    {
        $this->command->info('Seeding Scope 3 emissions...');

        // Get Scope 3 emission factors
        $scope3Factors = EmissionFactor::where('scope', 'scope_3')->get();

        if ($scope3Factors->isEmpty()) {
            $this->command->warn('  No Scope 3 emission factors found. Skipping Scope 3 seeding.');
            return;
        }

        $periodTarget = $target2025['scope3_total'] * $periodRatio;

        // Calculate total months in period
        $totalMonths = $startDate->copy()->diffInMonths($endDate) + 1;

        // Distribute target across categories
        $categoryWeights = [
            'upstream_transportation' => 0.034,   // 3.4%
            'waste_generated' => 0.218,           // 21.8%
            'business_travel' => 0.087,           // 8.7%
            'employee_commuting' => 0.265,        // 26.5%
            'downstream_transportation' => 0.394, // 39.4%
        ];

        $currentDate = $startDate->copy();
        $entriesCreated = 0;

        foreach ($categoryWeights as $category => $weight) {
            $categoryTarget = $periodTarget * $weight;

            // Find matching emission factor
            $searchTerm = ucwords(str_replace('_', ' ', $category));
            $factor = $scope3Factors->first(function ($item) use ($searchTerm) {
                return stripos($item->category, $searchTerm) !== false;
            });

            if (!$factor) {
                $this->command->warn("  No factor found for category: {$category}");
                continue;
            }

            $tempDate = $startDate->copy();

            while ($tempDate->lessThanOrEqualTo($endDate)) {
                $month = $tempDate->month;
                $year = $tempDate->year;

                // Entries per month varies by category
                $entriesPerMonth = match($category) {
                    'upstream_transportation', 'downstream_transportation' => rand(4, 6),
                    'business_travel' => rand(6, 8),
                    'employee_commuting' => rand(8, 10),
                    'waste_generated' => rand(3, 4),
                    default => 5,
                };

                $daysInMonth = $tempDate->daysInMonth;

                for ($i = 0; $i < $entriesPerMonth; $i++) {
                    $day = rand(1, min($daysInMonth, 28));
                    $entryDate = Carbon::create($year, $month, $day);

                    // Skip if entry date is beyond end date
                    if ($entryDate->greaterThan($endDate)) {
                        continue;
                    }

                    $emissionResult = ($categoryTarget / $totalMonths) / $entriesPerMonth;
                    $activityValue = $emissionResult / $factor->factor;

                    Scope3Emission::create([
                        'emission_factor_id' => $factor->id,
                        'stakeholder_id' => $stakeholders->random()->id,
                        'measurement_date' => $entryDate,
                        'category' => $category,
                        'activity_value' => round($activityValue, 2),
                        'activity_unit' => $this->getScope3Unit($category),
                        'emission_result' => round($emissionResult, 2),
                        'location' => $this->getScope3Location($category),
                        'notes' => $this->getScope3Note($category, $entryDate),
                        'created_by' => 1,
                    ]);

                    $entriesCreated++;
                }

                // Move to next month
                $tempDate->addMonth()->startOfMonth();
            }
        }

        $this->command->info("  Created {$entriesCreated} Scope 3 entries");
    }

    private function showSummary($startDate, $endDate): void
    {
        $this->command->info("\n=== Summary ===");
        $this->command->info("Period: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

        $scope1Total = Scope1Emission::whereBetween('measurement_date', [$startDate, $endDate])->sum('emission_result');
        $scope2Total = Scope2Emission::whereBetween('measurement_date', [$startDate, $endDate])->sum('emission_result');
        $scope3Total = Scope3Emission::whereBetween('measurement_date', [$startDate, $endDate])->sum('emission_result');
        $grandTotal = $scope1Total + $scope2Total + $scope3Total;

        // Convert kg to Ton for display
        $this->command->info("Scope 1: " . number_format($scope1Total / 1000, 2) . " Ton CO2eq");
        $this->command->info("Scope 2: " . number_format($scope2Total / 1000, 2) . " Ton CO2eq");
        $this->command->info("Scope 3: " . number_format($scope3Total / 1000, 2) . " Ton CO2eq");
        $this->command->info("Grand Total: " . number_format($grandTotal / 1000, 2) . " Ton CO2eq");

        // Show reduction progress
        $baseline2024Total = 752733.86; // Ton CO2eq
        $annualizedTotal = (($grandTotal / 1000) / $startDate->diffInDays($endDate)) * 365;
        $reductionPercent = (($baseline2024Total - $annualizedTotal) / $baseline2024Total) * 100;

        $this->command->info("\nAnnualized projection: " . number_format($annualizedTotal, 2) . " Ton CO2eq");
        $this->command->info("Progress towards 2030 target: " . number_format($reductionPercent, 2) . "% reduction from 2024 baseline");
    }

    private function getRandomLocation(): string
    {
        return ['Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Tangerang', 'Bekasi'][array_rand(['Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Tangerang', 'Bekasi'])];
    }

    private function getRandomRegion(): string
    {
        return ['Jabodetabek - BTS Cluster', 'Jawa Barat - BTS', 'Jawa Timur - BTS', 'Sumatera - BTS'][array_rand(['Jabodetabek - BTS Cluster', 'Jawa Barat - BTS', 'Jawa Timur - BTS', 'Sumatera - BTS'])];
    }

    private function getScope3Unit(string $category): string
    {
        return match($category) {
            'upstream_transportation', 'downstream_transportation' => 'Ton.Km',
            'business_travel', 'employee_commuting' => 'Km',
            'waste_generated' => 'Kg',
            default => 'Unit',
        };
    }

    private function getScope3Location(string $category): string
    {
        return match($category) {
            'upstream_transportation' => 'Jakarta - Regional',
            'downstream_transportation' => 'Distribution Center',
            'business_travel' => 'Domestic/International',
            'employee_commuting' => 'Jabodetabek Area',
            'waste_generated' => 'Kantor & BTS Sites',
            default => 'Various',
        };
    }

    private function getScope3Note(string $category, Carbon $date): string
    {
        $monthName = $date->format('F Y');
        return match($category) {
            'upstream_transportation' => "Distribusi peralatan jaringan - {$monthName}",
            'downstream_transportation' => "Distribusi produk customer - {$monthName}",
            'business_travel' => "Perjalanan dinas - {$monthName}",
            'employee_commuting' => "Perjalanan karyawan - {$monthName}",
            'waste_generated' => "Limbah operasional - {$monthName}",
            default => "Aktivitas Scope 3 - {$monthName}",
        };
    }
}
