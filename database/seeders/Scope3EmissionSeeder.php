<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Scope3Emission;
use App\Models\EmissionFactor;
use App\Models\Stakeholder;
use Carbon\Carbon;

class Scope3EmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Target Total Scope 3: 4.252,47 Ton CO2eq
     * Breakdown by category:
     * 1. Kategori 4 (Distribusi Hulu): 143,40 Ton CO2eq
     * 2. Kategori 5 (Limbah Operasional): 928,22 Ton CO2eq
     * 3. Kategori 6 (Perjalanan Bisnis): 371,12 Ton CO2eq
     * 4. Kategori 7 (Perjalanan Karyawan): 1.128,30 Ton CO2eq
     * 5. Kategori 9 (Distribusi Hilir): 1.676,65 Ton CO2eq
     * 6. Kategori 13 (Aset Sewa Hilir): 4,78 Ton CO2eq
     */
    public function run(): void
    {
        $year = 2024;

        // Get stakeholders
        $stakeholders = Stakeholder::all();
        if ($stakeholders->isEmpty()) {
            $this->command->error('Stakeholders not found. Please run StakeholderSeeder first.');
            return;
        }

        // Get emission factors by category
        $factors = [
            'upstream_transportation' => EmissionFactor::where('category', 'like', '%Upstream Transportation%')->first(),
            'waste_generated' => EmissionFactor::where('category', 'like', '%Waste Generated%')->first(),
            'business_travel' => EmissionFactor::where('category', 'like', '%Business Travel%')->first(),
            'employee_commuting' => EmissionFactor::where('category', 'like', '%Employee Commuting%')->first(),
            'downstream_transportation' => EmissionFactor::where('category', 'like', '%Downstream Transportation%')->first(),
        ];

        // Annual targets in kg CO2eq
        $annualTargets = [
            'upstream_transportation' => 143400,    // 143.40 Ton
            'waste_generated' => 928220,            // 928.22 Ton
            'business_travel' => 371120,            // 371.12 Ton
            'employee_commuting' => 1128300,        // 1,128.30 Ton
            'downstream_transportation' => 1676650, // 1,676.65 Ton
        ];

        // Generate data for each category
        foreach ($annualTargets as $category => $annualTarget) {
            $factor = $factors[$category];

            if (!$factor) {
                $this->command->warn("Emission factor for {$category} not found. Skipping...");
                continue;
            }

            $this->seedCategory($year, $category, $factor, $annualTarget, $stakeholders);
        }

        $this->command->info('Scope 3 emissions seeded successfully for 12 months of 2024!');

        // Show summary
        $totalEmission = Scope3Emission::sum('emission_result');
        $this->command->info("Total Scope 3 Emission: " . number_format($totalEmission, 2) . " kg CO2eq (" . number_format($totalEmission / 1000, 2) . " Ton CO2eq)");

        // Show breakdown by category
        $this->command->info("\nBreakdown by category:");
        foreach ($annualTargets as $category => $target) {
            $categoryTotal = Scope3Emission::where('category', $category)->sum('emission_result');
            $this->command->info("  {$category}: " . number_format($categoryTotal / 1000, 2) . " Ton CO2eq");
        }
    }

    private function seedCategory(int $year, string $category, $factor, float $annualTarget, $stakeholders): void
    {
        // Monthly distribution weights (some categories peak in certain months)
        $monthlyWeights = $this->getMonthlyWeights($category);

        foreach (range(1, 12) as $month) {
            $weight = $monthlyWeights[$month];
            $monthlyTarget = $annualTarget * $weight;

            // Number of entries per month varies by category
            $entriesPerMonth = $this->getEntriesPerMonth($category);
            $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

            for ($i = 0; $i < $entriesPerMonth; $i++) {
                $emissionResult = $monthlyTarget / $entriesPerMonth;

                // Calculate activity value based on emission result and factor
                $activityValue = $emissionResult / $factor->factor;

                $day = rand(1, min($daysInMonth, 28));

                Scope3Emission::create([
                    'emission_factor_id' => $factor->id,
                    'stakeholder_id' => $stakeholders->random()->id,
                    'measurement_date' => Carbon::create($year, $month, $day),
                    'category' => $category,
                    'activity_value' => round($activityValue, 2),
                    'activity_unit' => $this->getActivityUnit($category),
                    'emission_result' => round($emissionResult, 2),
                    'location' => $this->getRandomLocation($category),
                    'notes' => $this->getCategoryNote($category, $month),
                    'created_by' => 1,
                ]);
            }
        }
    }

    private function getMonthlyWeights(string $category): array
    {
        // Different distribution patterns based on category nature
        switch ($category) {
            case 'upstream_transportation':
            case 'downstream_transportation':
                // Higher in Q2 and Q4 (business activity peaks)
                return [
                    1 => 0.070, 2 => 0.075, 3 => 0.085, 4 => 0.090,
                    5 => 0.092, 6 => 0.095, 7 => 0.085, 8 => 0.082,
                    9 => 0.080, 10 => 0.088, 11 => 0.092, 12 => 0.066,
                ];

            case 'business_travel':
                // Lower in January and December (holidays), peak in middle months
                return [
                    1 => 0.065, 2 => 0.078, 3 => 0.085, 4 => 0.088,
                    5 => 0.090, 6 => 0.092, 7 => 0.095, 8 => 0.093,
                    9 => 0.090, 10 => 0.087, 11 => 0.083, 12 => 0.054,
                ];

            case 'employee_commuting':
                // Relatively consistent, slightly lower in December
                return [
                    1 => 0.082, 2 => 0.083, 3 => 0.084, 4 => 0.084,
                    5 => 0.085, 6 => 0.085, 7 => 0.085, 8 => 0.084,
                    9 => 0.084, 10 => 0.084, 11 => 0.083, 12 => 0.077,
                ];

            case 'waste_generated':
                // Relatively even throughout year
                return [
                    1 => 0.082, 2 => 0.083, 3 => 0.084, 4 => 0.083,
                    5 => 0.084, 6 => 0.085, 7 => 0.085, 8 => 0.084,
                    9 => 0.083, 10 => 0.084, 11 => 0.083, 12 => 0.080,
                ];

            default:
                // Default even distribution
                return array_fill(1, 12, 1/12);
        }
    }

    private function getEntriesPerMonth(string $category): int
    {
        // Different entry frequency based on category
        return match($category) {
            'upstream_transportation', 'downstream_transportation' => rand(4, 6),
            'business_travel' => rand(8, 12),
            'employee_commuting' => rand(10, 15),
            'waste_generated' => rand(3, 5),
            default => 5,
        };
    }

    private function getActivityUnit(string $category): string
    {
        return match($category) {
            'upstream_transportation', 'downstream_transportation' => 'Ton.Km',
            'business_travel' => 'Km',
            'employee_commuting' => 'Km',
            'waste_generated' => 'Kg',
            default => 'Unit',
        };
    }

    private function getRandomLocation(string $category): string
    {
        $locations = match($category) {
            'upstream_transportation' => [
                'Jakarta - Bandung',
                'Jakarta - Surabaya',
                'Jakarta - Medan',
                'Surabaya - Malang',
                'Jakarta Port - Warehouse',
            ],
            'downstream_transportation' => [
                'Distribution Center Jakarta',
                'Distribution Center Surabaya',
                'Distribution Center Medan',
                'Last Mile Delivery Jabodetabek',
                'Last Mile Delivery Regional',
            ],
            'business_travel' => [
                'Jakarta - Surabaya',
                'Jakarta - Bali',
                'Jakarta - Singapore',
                'Jakarta - Medan',
                'Domestic Flight',
                'International Flight',
            ],
            'employee_commuting' => [
                'Jabodetabek - Kantor Pusat',
                'Regional Office - Staff',
                'Remote Area - Field Staff',
            ],
            'waste_generated' => [
                'Kantor Pusat Jakarta',
                'BTS Site Maintenance',
                'Regional Office',
                'E-waste Collection Center',
            ],
            default => 'Various Locations',
        };

        return $locations[array_rand($locations)];
    }

    private function getCategoryNote(string $category, int $month): string
    {
        $monthName = Carbon::create(2024, $month, 1)->format('F Y');

        $notes = match($category) {
            'upstream_transportation' => "Distribusi peralatan & komponen jaringan - {$monthName}",
            'downstream_transportation' => "Distribusi produk ke customer & dealer - {$monthName}",
            'business_travel' => "Perjalanan dinas karyawan (pesawat & darat) - {$monthName}",
            'employee_commuting' => "Perjalanan pulang-pergi karyawan - {$monthName}",
            'waste_generated' => "Limbah operasional (B3 & E-waste) - {$monthName}",
            default => "Aktivitas Scope 3 - {$monthName}",
        };

        return $notes;
    }
}
