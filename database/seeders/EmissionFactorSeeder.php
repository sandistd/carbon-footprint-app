<?php

namespace Database\Seeders;

use App\Models\EmissionFactor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmissionFactorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $factors = [
            // Scope 1: Emisi Langsung
            [
                'name' => 'Solar (Diesel)',
                'scope' => 'scope_1',
                'category' => 'Stationary Combustion',
                'factor' => 2.68,
                'unit' => 'kg CO2eq/Liter',
                'description' => 'Faktor emisi untuk pembakaran solar/diesel di genset BTS',
                'source' => 'The Greenhouse Gas Protocol Initiative (2004)',
                'is_active' => true,
            ],
            [
                'name' => 'Bensin (Pertalite/Gasoline)',
                'scope' => 'scope_1',
                'category' => 'Mobile Combustion',
                'factor' => 2.31,
                'unit' => 'kg CO2eq/Liter',
                'description' => 'Faktor emisi untuk pembakaran bensin di kendaraan operasional',
                'source' => 'The Greenhouse Gas Protocol Initiative (2004)',
                'is_active' => true,
            ],

            // Scope 2: Emisi Energi Tidak Langsung (Listrik)
            [
                'name' => 'Listrik PLN (Grid)',
                'scope' => 'scope_2',
                'category' => 'Purchased Electricity',
                'factor' => 0.78,
                'unit' => 'kg CO2eq/KWh',
                'description' => 'Faktor emisi grid listrik Indonesia (PLN) rata-rata',
                'source' => 'PLN/MEMR Grid Emission Factor',
                'is_active' => true,
            ],

            // Scope 3: Emisi Lainnya (Rantai Pasok)
            [
                'name' => 'Distribusi Hulu (Transport)',
                'scope' => 'scope_3',
                'category' => 'Kategori 4: Upstream Transportation',
                'factor' => 0.15,
                'unit' => 'kg CO2eq/Km',
                'description' => 'Faktor emisi untuk transportasi distribusi upstream',
                'source' => 'GHG Protocol Scope 3 Standard',
                'is_active' => true,
            ],
            [
                'name' => 'Limbah B3',
                'scope' => 'scope_3',
                'category' => 'Kategori 5: Waste Generated in Operations',
                'factor' => 1.5,
                'unit' => 'kg CO2eq/Kg',
                'description' => 'Faktor emisi untuk pengolahan limbah B3',
                'source' => 'GHG Protocol Scope 3 Standard',
                'is_active' => true,
            ],
            [
                'name' => 'Limbah Elektronik (E-waste)',
                'scope' => 'scope_3',
                'category' => 'Kategori 5: Waste Generated in Operations',
                'factor' => 2.0,
                'unit' => 'kg CO2eq/Kg',
                'description' => 'Faktor emisi untuk pengolahan limbah elektronik',
                'source' => 'GHG Protocol Scope 3 Standard',
                'is_active' => true,
            ],
            [
                'name' => 'Perjalanan Bisnis (Penerbangan)',
                'scope' => 'scope_3',
                'category' => 'Kategori 6: Business Travel',
                'factor' => 0.25,
                'unit' => 'kg CO2eq/Km',
                'description' => 'Faktor emisi untuk perjalanan bisnis menggunakan pesawat',
                'source' => 'GHG Protocol Scope 3 Standard',
                'is_active' => true,
            ],
            [
                'name' => 'Perjalanan Bisnis (Darat)',
                'scope' => 'scope_3',
                'category' => 'Kategori 6: Business Travel',
                'factor' => 0.12,
                'unit' => 'kg CO2eq/Km',
                'description' => 'Faktor emisi untuk perjalanan bisnis menggunakan kendaraan darat',
                'source' => 'GHG Protocol Scope 3 Standard',
                'is_active' => true,
            ],
            [
                'name' => 'Perjalanan Karyawan (Commuting)',
                'scope' => 'scope_3',
                'category' => 'Kategori 7: Employee Commuting',
                'factor' => 0.15,
                'unit' => 'kg CO2eq/Km',
                'description' => 'Faktor emisi untuk perjalanan pulang-pergi karyawan',
                'source' => 'GHG Protocol Scope 3 Standard',
                'is_active' => true,
            ],
            [
                'name' => 'Distribusi Hilir (Transport)',
                'scope' => 'scope_3',
                'category' => 'Kategori 9: Downstream Transportation',
                'factor' => 0.18,
                'unit' => 'kg CO2eq/Km',
                'description' => 'Faktor emisi untuk transportasi distribusi downstream',
                'source' => 'GHG Protocol Scope 3 Standard',
                'is_active' => true,
            ],
            [
                'name' => 'Aset Sewa Hilir',
                'scope' => 'scope_3',
                'category' => 'Kategori 13: Downstream Leased Assets',
                'factor' => 0.5,
                'unit' => 'kg CO2eq/Unit',
                'description' => 'Faktor emisi untuk aset yang disewakan downstream',
                'source' => 'GHG Protocol Scope 3 Standard',
                'is_active' => true,
            ],
        ];

        foreach ($factors as $factor) {
            EmissionFactor::create($factor);
        }
    }
}
