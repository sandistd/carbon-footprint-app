# Database Seeders - Carbon Footprint Application

## Overview
This directory contains seeders that populate the database with emission data for PT XL Axiata Tbk based on their 2024 Sustainability Report.

## Seeder Files

### 1. StakeholderSeeder.php
Seeds stakeholder data including departments and responsible parties for emission tracking.

### 2. EmissionFactorSeeder.php
Seeds emission factors for all three scopes:
- **Scope 1**: Solar (Diesel), Bensin (Pertalite/Gasoline)
- **Scope 2**: Listrik PLN (Grid)
- **Scope 3**: Various categories (Transportation, Waste, Business Travel, etc.)

### 3. Scope1EmissionSeeder.php
Seeds direct emissions data for 2024:
- **Target**: 4,082.53 Ton CO2eq
- **Data Breakdown**:
  - Solar (Diesel): 1,189,149.24 Liter
  - Bensin (Pertalite): 275,593.70 Liter
- **Distribution**: Monthly breakdown with 3-4 entries per month (84 total entries)
- **Effective Factors**: Includes N2O & CH4 adjustments from external auditor

### 4. Scope2EmissionSeeder.php
Seeds indirect emissions from energy consumption for 2024:
- **Target**: 744,398.86 Ton CO2eq
- **Data Breakdown**:
  - Electricity Consumption: 956,319,188.94 KWh
  - REC (Renewable Energy Certificate): 1,000,000 KWh
  - Net Grid: 955,319,188.94 KWh
- **Distribution**: Monthly breakdown with 5-6 entries per month (68 total entries)
- **Effective Factor**: 0.779215 kg CO2eq/KWh

### 5. Scope3EmissionSeeder.php
Seeds other indirect emissions for 2024:
- **Target**: 4,252.47 Ton CO2eq (Achieved: 4,247.69 Ton - 99.89% accurate)
- **Data Breakdown**:
  - Kategori 4 (Upstream Transportation): 143.40 Ton CO2eq
  - Kategori 5 (Waste Generated): 928.22 Ton CO2eq
  - Kategori 6 (Business Travel): 371.12 Ton CO2eq
  - Kategori 7 (Employee Commuting): 1,128.30 Ton CO2eq
  - Kategori 9 (Downstream Transportation): 1,676.65 Ton CO2eq
- **Distribution**: Variable entries per month based on category (446 total entries)

## Running the Seeders

### Fresh Migration with Seeding
```bash
php artisan migrate:fresh --seed
```

### Run Specific Seeder
```bash
php artisan db:seed --class=Scope1EmissionSeeder
```

## Data Accuracy

| Scope | Target (Ton CO2eq) | Achieved (Ton CO2eq) | Accuracy |
|-------|-------------------|---------------------|----------|
| Scope 1 | 4,082.53 | 4,082.53 | 100.00% ✓ |
| Scope 2 | 744,398.86 | 744,398.86 | 100.00% ✓ |
| Scope 3 | 4,252.47 | 4,247.69 | 99.89% ✓ |
| **TOTAL** | **752,733.86** | **752,729.08** | **99.99%** ✓ |

## Data Distribution
- **Date Range**: January 1, 2024 - December 31, 2024
- **Total Entries**: 598 emission records
  - Scope 1: 84 entries
  - Scope 2: 68 entries
  - Scope 3: 446 entries

## Monthly Distribution Pattern
Each seeder uses weighted monthly distribution:
- **Scope 1**: Higher consumption in mid-year (June-August) due to peak operations
- **Scope 2**: Relatively consistent throughout the year (continuous BTS operations)
- **Scope 3**: Varies by category:
  - Business Travel: Lower in January & December (holidays)
  - Employee Commuting: Relatively consistent
  - Transportation: Peak in Q2 and Q4
  - Waste: Even distribution throughout year

## References
- Source: PT XL Axiata Tbk Sustainability Report 2024
- Calculation Details: `.copilot/calculation.md`
- Standards: GHG Protocol, IPCC Guidelines
- Emission Factors: The Greenhouse Gas Protocol Initiative (2004), PLN/MEMR Grid Emission Factor

## Notes
- REC (Renewable Energy Certificate) of 1,000,000 KWh is applied in June 2024 entries
- Effective emission factors include adjustments for N2O and CH4 from external auditor
- All dates are randomized within each month to simulate real-world data collection
- Stakeholders are randomly assigned to demonstrate multi-department tracking
