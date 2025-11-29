<?php

namespace App\Http\Controllers;

use App\Models\EmissionFactor;
use App\Models\Scope3Emission;
use App\Models\Stakeholder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class Scope3EmissionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Scope3Emission::with(['emissionFactor', 'stakeholder', 'creator']);

        // Filter by emission factor
        if ($request->filled('emission_factor_id')) {
            $query->where('emission_factor_id', $request->emission_factor_id);
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->whereHas('stakeholder', fn ($q) => $q->where('department', $request->department));
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('measurement_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('measurement_date', '<=', $request->date_to);
        }

        $emissions = $query->orderBy('measurement_date', 'desc')->paginate(15);

        $emissionFactors = EmissionFactor::active()
            ->byScope('scope_3')
            ->orderBy('name')
            ->get();

        // Get unique departments for filter dropdown
        $departments = Stakeholder::select('department')
            ->distinct()
            ->whereNotNull('department')
            ->orderBy('department')
            ->pluck('department');

        // Categories from calculation.md
        $categories = [
            'Kategori 4: Distribusi Hulu',
            'Kategori 5: Limbah Operasional',
            'Kategori 6: Perjalanan Bisnis',
            'Kategori 7: Perjalanan Pulang-Pergi Karyawan',
            'Kategori 9: Distribusi Hilir',
            'Kategori 13: Aset Sewa Hilir',
        ];

        return Inertia::render('scope-3/index', [
            'emissions' => $emissions,
            'emissionFactors' => $emissionFactors,
            'departments' => $departments,
            'categories' => $categories,
            'filters' => [
                'emission_factor_id' => $request->emission_factor_id,
                'department' => $request->department,
                'category' => $request->category,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ],
        ]);
    }

    public function create(): Response
    {
        $emissionFactors = EmissionFactor::active()
            ->byScope('scope_3')
            ->orderBy('name')
            ->get();

        $stakeholders = Stakeholder::orderBy('name')->get();

        $categories = [
            'Kategori 4: Distribusi Hulu',
            'Kategori 5: Limbah Operasional',
            'Kategori 6: Perjalanan Bisnis',
            'Kategori 7: Perjalanan Pulang-Pergi Karyawan',
            'Kategori 9: Distribusi Hilir',
            'Kategori 13: Aset Sewa Hilir',
        ];

        return Inertia::render('scope-3/create', [
            'emissionFactors' => $emissionFactors,
            'stakeholders' => $stakeholders,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'emission_factor_id' => 'required|exists:emission_factors,id',
            'stakeholder_id' => 'nullable|exists:stakeholders,id',
            'category' => 'required|string|max:255',
            'measurement_date' => 'required|date',
            'activity_value' => 'required|numeric|min:0',
            'activity_unit' => 'required|string|max:50',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();

        // Create emission
        $emission = new Scope3Emission($validated);
        $emission->emission_factor_id = $validated['emission_factor_id'];
        $emission->save();

        // Calculate emission result
        $emission->load('emissionFactor');
        $emission->setAttribute('emission_result', round($emission->calculateEmission(), 2));
        $emission->save();

        return redirect()->route('scope-3.index')
            ->with('success', 'Data emisi Scope 3 berhasil ditambahkan.');
    }

    public function edit(Scope3Emission $scope_3): Response
    {
        $scope_3->load(['emissionFactor', 'stakeholder']);

        $emissionFactors = EmissionFactor::active()
            ->byScope('scope_3')
            ->orderBy('name')
            ->get();

        $stakeholders = Stakeholder::orderBy('name')->get();

        $categories = [
            'Kategori 4: Distribusi Hulu',
            'Kategori 5: Limbah Operasional',
            'Kategori 6: Perjalanan Bisnis',
            'Kategori 7: Perjalanan Pulang-Pergi Karyawan',
            'Kategori 9: Distribusi Hilir',
            'Kategori 13: Aset Sewa Hilir',
        ];

        return Inertia::render('scope-3/edit', [
            'emission' => $scope_3,
            'emissionFactors' => $emissionFactors,
            'stakeholders' => $stakeholders,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Scope3Emission $scope_3): RedirectResponse
    {
        $validated = $request->validate([
            'emission_factor_id' => 'required|exists:emission_factors,id',
            'stakeholder_id' => 'nullable|exists:stakeholders,id',
            'category' => 'required|string|max:255',
            'measurement_date' => 'required|date',
            'activity_value' => 'required|numeric|min:0',
            'activity_unit' => 'required|string|max:50',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $scope_3->update($validated);

        // Recalculate emission result
        $scope_3->load('emissionFactor');
        $scope_3->setAttribute('emission_result', round($scope_3->calculateEmission(), 2));
        $scope_3->save();

        return redirect()->route('scope-3.index')
            ->with('success', 'Data emisi Scope 3 berhasil diperbarui.');
    }

    public function destroy(Scope3Emission $scope_3): RedirectResponse
    {
        $scope_3->delete();

        return redirect()->route('scope-3.index')
            ->with('success', 'Data emisi Scope 3 berhasil dihapus.');
    }
}
