<?php

namespace App\Http\Controllers;

use App\Models\EmissionFactor;
use App\Models\Scope2Emission;
use App\Models\Stakeholder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class Scope2EmissionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Scope2Emission::with(['emissionFactor', 'stakeholder', 'creator']);

        // Filter by emission factor
        if ($request->filled('emission_factor_id')) {
            $query->where('emission_factor_id', $request->emission_factor_id);
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->whereHas('stakeholder', fn ($q) => $q->where('department', $request->department));
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
            ->byScope('scope_2')
            ->orderBy('name')
            ->get();

        // Get unique departments for filter dropdown
        $departments = Stakeholder::select('department')
            ->distinct()
            ->whereNotNull('department')
            ->orderBy('department')
            ->pluck('department');

        return Inertia::render('scope-2/index', [
            'emissions' => $emissions,
            'emissionFactors' => $emissionFactors,
            'departments' => $departments,
            'filters' => [
                'emission_factor_id' => $request->emission_factor_id,
                'department' => $request->department,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ],
        ]);
    }

    public function create(): Response
    {
        $emissionFactors = EmissionFactor::active()
            ->byScope('scope_2')
            ->orderBy('name')
            ->get();

        $stakeholders = Stakeholder::orderBy('name')->get();

        return Inertia::render('scope-2/create', [
            'emissionFactors' => $emissionFactors,
            'stakeholders' => $stakeholders,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'emission_factor_id' => 'required|exists:emission_factors,id',
            'stakeholder_id' => 'nullable|exists:stakeholders,id',
            'measurement_date' => 'required|date',
            'activity_value' => 'required|numeric|min:0',
            'activity_unit' => 'required|string|max:50',
            'rec_value' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['rec_value'] = $validated['rec_value'] ?? 0;

        // Create emission
        $emission = new Scope2Emission($validated);
        $emission->emission_factor_id = $validated['emission_factor_id'];
        $emission->save();

        // Calculate emission result
        $emission->load('emissionFactor');
        $emission->setAttribute('emission_result', round($emission->calculateEmission(), 2));
        $emission->save();

        return redirect()->route('scope-2.index')
            ->with('success', 'Data emisi Scope 2 berhasil ditambahkan.');
    }

    public function edit(Scope2Emission $scope_2): Response
    {
        $scope_2->load(['emissionFactor', 'stakeholder']);

        $emissionFactors = EmissionFactor::active()
            ->byScope('scope_2')
            ->orderBy('name')
            ->get();

        $stakeholders = Stakeholder::orderBy('name')->get();

        return Inertia::render('scope-2/edit', [
            'emission' => $scope_2,
            'emissionFactors' => $emissionFactors,
            'stakeholders' => $stakeholders,
        ]);
    }

    public function update(Request $request, Scope2Emission $scope_2): RedirectResponse
    {
        $validated = $request->validate([
            'emission_factor_id' => 'required|exists:emission_factors,id',
            'stakeholder_id' => 'nullable|exists:stakeholders,id',
            'measurement_date' => 'required|date',
            'activity_value' => 'required|numeric|min:0',
            'activity_unit' => 'required|string|max:50',
            'rec_value' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['rec_value'] = $validated['rec_value'] ?? 0;

        $scope_2->update($validated);

        // Recalculate emission result
        $scope_2->load('emissionFactor');
        $scope_2->setAttribute('emission_result', round($scope_2->calculateEmission(), 2));
        $scope_2->save();

        return redirect()->route('scope-2.index')
            ->with('success', 'Data emisi Scope 2 berhasil diperbarui.');
    }

    public function destroy(Scope2Emission $scope_2): RedirectResponse
    {
        $scope_2->delete();

        return redirect()->route('scope-2.index')
            ->with('success', 'Data emisi Scope 2 berhasil dihapus.');
    }
}
