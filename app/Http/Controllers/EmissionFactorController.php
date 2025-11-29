<?php

namespace App\Http\Controllers;

use App\Models\EmissionFactor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class EmissionFactorController extends Controller
{
    public function index(Request $request): Response
    {
        $scope = $request->input('scope');

        $factors = EmissionFactor::query()
            ->when($scope, fn($q) => $q->where('scope', $scope))
            ->orderBy('scope')
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('konfigurasi/index', [
            'factors' => $factors,
            'filters' => ['scope' => $scope],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('konfigurasi/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'scope' => 'required|string|in:scope_1,scope_2,scope_3',
            'category' => 'nullable|string|max:255',
            'factor' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'source' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        EmissionFactor::create($validated);

        return redirect()->route('konfigurasi.index')
            ->with('success', 'Faktor emisi berhasil ditambahkan.');
    }

    public function edit(EmissionFactor $konfigurasi): Response
    {
        return Inertia::render('konfigurasi/edit', [
            'factor' => $konfigurasi,
        ]);
    }

    public function update(Request $request, EmissionFactor $konfigurasi): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'scope' => 'required|string|in:scope_1,scope_2,scope_3',
            'category' => 'nullable|string|max:255',
            'factor' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'source' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $konfigurasi->update($validated);

        return redirect()->route('konfigurasi.index')
            ->with('success', 'Faktor emisi berhasil diperbarui.');
    }

    public function destroy(EmissionFactor $konfigurasi): RedirectResponse
    {
        $konfigurasi->delete();

        return redirect()->route('konfigurasi.index')
            ->with('success', 'Faktor emisi berhasil dihapus.');
    }
}
