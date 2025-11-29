<?php

namespace App\Http\Controllers;

use App\Models\Scope1Emission;
use App\Models\EmissionFactor;
use App\Models\Stakeholder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class Scope1EmissionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Scope1Emission::with(['emissionFactor', 'stakeholder', 'creator']);

        // Filter by emission factor
        if ($request->filled('emission_factor_id')) {
            $query->where('emission_factor_id', $request->emission_factor_id);
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->whereHas('stakeholder', fn($q) => $q->where('department', $request->department));
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
            ->byScope('scope_1')
            ->orderBy('name')
            ->get();

        $stakeholders = Stakeholder::orderBy('name')->get();

        // Get unique departments for filter dropdown
        $departments = Stakeholder::select('department')
            ->distinct()
            ->whereNotNull('department')
            ->orderBy('department')
            ->pluck('department');

        return Inertia::render('scope-1/index', [
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
            ->byScope('scope_1')
            ->orderBy('name')
            ->get();

        $stakeholders = Stakeholder::orderBy('name')->get();

        return Inertia::render('scope-1/create', [
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
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();

        // Create emission
        $emission = new Scope1Emission($validated);
        $emission->emission_factor_id = $validated['emission_factor_id'];
        $emission->save();

        // Calculate emission result
        $emission->load('emissionFactor');
        $emission->setAttribute('emission_result', $emission->calculateEmission());
        $emission->save();

        return redirect()->route('scope-1.index')
            ->with('success', 'Data emisi Scope 1 berhasil ditambahkan.');
    }

    public function edit(Scope1Emission $scope_1): Response
    {
        $scope_1->load(['emissionFactor', 'stakeholder']);

        $emissionFactors = EmissionFactor::active()
            ->byScope('scope_1')
            ->orderBy('name')
            ->get();

        $stakeholders = Stakeholder::orderBy('name')->get();

        return Inertia::render('scope-1/edit', [
            'emission' => $scope_1,
            'emissionFactors' => $emissionFactors,
            'stakeholders' => $stakeholders,
        ]);
    }

    public function update(Request $request, Scope1Emission $scope_1): RedirectResponse
    {
        $validated = $request->validate([
            'emission_factor_id' => 'required|exists:emission_factors,id',
            'stakeholder_id' => 'nullable|exists:stakeholders,id',
            'measurement_date' => 'required|date',
            'activity_value' => 'required|numeric|min:0',
            'activity_unit' => 'required|string|max:50',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $scope_1->update($validated);

        // Recalculate emission result
        $scope_1->load('emissionFactor');
        $scope_1->setAttribute('emission_result', $scope_1->calculateEmission());
        $scope_1->save();

        return redirect()->route('scope-1.index')
            ->with('success', 'Data emisi Scope 1 berhasil diperbarui.');
    }

    public function destroy(Scope1Emission $scope_1): RedirectResponse
    {
        $scope_1->delete();

        return redirect()->route('scope-1.index')
            ->with('success', 'Data emisi Scope 1 berhasil dihapus.');
    }
}
