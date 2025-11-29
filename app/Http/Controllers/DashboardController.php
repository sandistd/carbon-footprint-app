<?php

namespace App\Http\Controllers;

use App\Models\Scope1Emission;
use App\Models\Scope2Emission;
use App\Models\Scope3Emission;
use App\Models\Stakeholder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $scope = $request->input('scope', 'all'); // all, scope_1, scope_2, scope_3
        $department = $request->input('department');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $year = $request->input('year'); // Filter tahun untuk pie chart

        // Get unique departments for filter dropdown
        $departments = Stakeholder::select('department')
            ->distinct()
            ->whereNotNull('department')
            ->orderBy('department')
            ->pluck('department');

        // Get available years from emissions data
        // Use strftime for SQLite compatibility, YEAR() for MySQL
        $yearExpression = DB::getDriverName() === 'sqlite'
            ? "strftime('%Y', measurement_date)"
            : "YEAR(measurement_date)";

        $availableYears = collect();
        $availableYears = $availableYears->merge(
            Scope1Emission::selectRaw("{$yearExpression} as year")
                ->distinct()
                ->pluck('year')
        );
        $availableYears = $availableYears->merge(
            Scope2Emission::selectRaw("{$yearExpression} as year")
                ->distinct()
                ->pluck('year')
        );
        $availableYears = $availableYears->merge(
            Scope3Emission::selectRaw("{$yearExpression} as year")
                ->distinct()
                ->pluck('year')
        );
        $availableYears = $availableYears->unique()->sort()->values();

        // Build queries with filters
        $scope1Query = Scope1Emission::with(['emissionFactor', 'stakeholder'])
            ->when($department, function($q) use ($department) {
                $q->whereHas('stakeholder', fn($query) => $query->where('department', $department));
            })
            ->when($startDate, fn($q) => $q->whereDate('measurement_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('measurement_date', '<=', $endDate))
            ->orderBy('measurement_date', 'desc');

        $scope2Query = Scope2Emission::with(['emissionFactor', 'stakeholder'])
            ->when($department, function($q) use ($department) {
                $q->whereHas('stakeholder', fn($query) => $query->where('department', $department));
            })
            ->when($startDate, fn($q) => $q->whereDate('measurement_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('measurement_date', '<=', $endDate))
            ->orderBy('measurement_date', 'desc');

        $scope3Query = Scope3Emission::with(['emissionFactor', 'stakeholder'])
            ->when($department, function($q) use ($department) {
                $q->whereHas('stakeholder', fn($query) => $query->where('department', $department));
            })
            ->when($startDate, fn($q) => $q->whereDate('measurement_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('measurement_date', '<=', $endDate))
            ->orderBy('measurement_date', 'desc');

        // Calculate totals based on scope filter
        $data = [];

        if ($scope === 'all' || $scope === 'scope_1') {
            $scope1Total = (clone $scope1Query)->sum('emission_result');
            $scope1Count = (clone $scope1Query)->count();
            $data['scope_1'] = [
                'total' => round($scope1Total / 1000, 2), // Convert kg to Ton
                'count' => $scope1Count,
                'emissions' => $scope === 'scope_1' ? $scope1Query->paginate(10) : null,
            ];
        }

        if ($scope === 'all' || $scope === 'scope_2') {
            $scope2Total = (clone $scope2Query)->sum('emission_result');
            $scope2Count = (clone $scope2Query)->count();
            $data['scope_2'] = [
                'total' => round($scope2Total / 1000, 2), // Convert kg to Ton
                'count' => $scope2Count,
                'emissions' => $scope === 'scope_2' ? $scope2Query->paginate(10) : null,
            ];
        }

        if ($scope === 'all' || $scope === 'scope_3') {
            $scope3Total = (clone $scope3Query)->sum('emission_result');
            $scope3Count = (clone $scope3Query)->count();
            $data['scope_3'] = [
                'total' => round($scope3Total / 1000, 2), // Convert kg to Ton
                'count' => $scope3Count,
                'emissions' => $scope === 'scope_3' ? $scope3Query->paginate(10) : null,
            ];
        }

        // Calculate grand total
        $grandTotal = 0;
        if (isset($data['scope_1'])) $grandTotal += $data['scope_1']['total'];
        if (isset($data['scope_2'])) $grandTotal += $data['scope_2']['total'];
        if (isset($data['scope_3'])) $grandTotal += $data['scope_3']['total'];

        // Pie chart data - filtered by year (convert kg to Ton)
        $pieChartData = [];
        if ($year) {
            $scope1Year = Scope1Emission::whereYear('measurement_date', $year)->sum('emission_result') / 1000;
            $scope2Year = Scope2Emission::whereYear('measurement_date', $year)->sum('emission_result') / 1000;
            $scope3Year = Scope3Emission::whereYear('measurement_date', $year)->sum('emission_result') / 1000;

            $pieChartData = [
                ['name' => 'Scope 1', 'value' => round($scope1Year, 2), 'color' => '#f97316'],
                ['name' => 'Scope 2', 'value' => round($scope2Year, 2), 'color' => '#eab308'],
                ['name' => 'Scope 3', 'value' => round($scope3Year, 2), 'color' => '#22c55e'],
            ];
        }

        // Target 2030 data - baseline 2024, target 45% reduction (convert kg to Ton)
        // Calculate yearly emissions from 2024 to 2030
        $baseline2024 = (Scope1Emission::whereYear('measurement_date', 2024)->sum('emission_result')
            + Scope2Emission::whereYear('measurement_date', 2024)->sum('emission_result')
            + Scope3Emission::whereYear('measurement_date', 2024)->sum('emission_result')) / 1000;

        // If no data in 2024, use current year or total
        if ($baseline2024 == 0) {
            $baseline2024 = $grandTotal > 0 ? $grandTotal : 752733.86; // Use 2024 report data as fallback
        }

        $target2030 = $baseline2024 * (1 - 0.45); // 45% reduction

        // Calculate yearly data for the chart
        $targetChartData = [];
        $currentYear = date('Y');

        for ($y = 2024; $y <= 2030; $y++) {
            // Linear reduction target from baseline to 2030
            $yearsDiff = 2030 - 2024; // 6 years
            $reductionPerYear = ($baseline2024 - $target2030) / $yearsDiff;
            $targetValue = $baseline2024 - (($y - 2024) * $reductionPerYear);

            // Actual data if year has passed (convert kg to Ton)
            $actualValue = null;
            if ($y <= $currentYear) {
                $actualValue = (Scope1Emission::whereYear('measurement_date', $y)->sum('emission_result')
                    + Scope2Emission::whereYear('measurement_date', $y)->sum('emission_result')
                    + Scope3Emission::whereYear('measurement_date', $y)->sum('emission_result')) / 1000;

                // If no data for that year, set to null
                if ($actualValue == 0 && $y != $currentYear) {
                    $actualValue = null;
                }
            }

            $targetChartData[] = [
                'year' => (string)$y,
                'target' => round($targetValue, 2),
                'actual' => $actualValue ? round($actualValue, 2) : null,
            ];
        }

        // Stakeholder distribution data (SQLite/MySQL compatible)
        $stakeholderChartData = [];
        if ($year) {
            // Determine YEAR function based on driver
            $yearFunc = DB::getDriverName() === 'sqlite'
                ? "CAST(strftime('%Y', {table}.measurement_date) AS INTEGER)"
                : "YEAR({table}.measurement_date)";

            $scope1YearFunc = str_replace('{table}', 'scope_1_emissions', $yearFunc);
            $scope2YearFunc = str_replace('{table}', 'scope_2_emissions', $yearFunc);
            $scope3YearFunc = str_replace('{table}', 'scope_3_emissions', $yearFunc);

            $stakeholderEmissions = Stakeholder::leftJoin('scope_1_emissions', 'stakeholders.id', '=', 'scope_1_emissions.stakeholder_id')
                ->leftJoin('scope_2_emissions', 'stakeholders.id', '=', 'scope_2_emissions.stakeholder_id')
                ->leftJoin('scope_3_emissions', 'stakeholders.id', '=', 'scope_3_emissions.stakeholder_id')
                ->selectRaw("stakeholders.department,
                    (COALESCE(SUM(CASE WHEN {$scope1YearFunc} = ? THEN scope_1_emissions.emission_result ELSE 0 END), 0) +
                     COALESCE(SUM(CASE WHEN {$scope2YearFunc} = ? THEN scope_2_emissions.emission_result ELSE 0 END), 0) +
                     COALESCE(SUM(CASE WHEN {$scope3YearFunc} = ? THEN scope_3_emissions.emission_result ELSE 0 END), 0)) / 1000 as total",
                    [$year, $year, $year])
                ->groupBy('stakeholders.department')
                ->havingRaw('total > 0')
                ->get();

            foreach ($stakeholderEmissions as $sh) {
                $stakeholderChartData[] = [
                    'name' => $sh->department ?: 'N/A',
                    'value' => round($sh->total, 2),
                ];
            }
        }

        // Monthly emissions data (for current selected year or all)
        $monthlyChartData = [];
        $yearForMonthly = $year ?: $currentYear;

        for ($m = 1; $m <= 12; $m++) {
            $monthName = date('M', mktime(0, 0, 0, $m, 1));

            $monthlyTotal = (Scope1Emission::whereYear('measurement_date', $yearForMonthly)
                    ->whereMonth('measurement_date', $m)
                    ->sum('emission_result') +
                Scope2Emission::whereYear('measurement_date', $yearForMonthly)
                    ->whereMonth('measurement_date', $m)
                    ->sum('emission_result') +
                Scope3Emission::whereYear('measurement_date', $yearForMonthly)
                    ->whereMonth('measurement_date', $m)
                    ->sum('emission_result')) / 1000;

            $monthlyChartData[] = [
                'month' => $monthName,
                'total' => round($monthlyTotal, 2),
            ];
        }

        return Inertia::render('dashboard', [
            'data' => $data,
            'grandTotal' => round($grandTotal, 2),
            'departments' => $departments,
            'filters' => [
                'scope' => $scope,
                'department' => $department,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'year' => $year,
            ],
            'availableYears' => $availableYears,
            'pieChartData' => $pieChartData,
            'targetChartData' => $targetChartData,
            'stakeholderChartData' => $stakeholderChartData,
            'monthlyChartData' => $monthlyChartData,
            'baseline2024' => round($baseline2024, 2),
            'target2030' => round($target2030, 2),
        ]);
    }
}
