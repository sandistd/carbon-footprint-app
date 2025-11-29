<?php

namespace App\Http\Controllers;

use App\Models\Stakeholder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StakeholderController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Stakeholder::query();

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%");
            });
        }

        $stakeholders = $query->orderBy('id', 'desc')->paginate(15);

        return Inertia::render('stakeholders/index', [
            'stakeholders' => $stakeholders,
            'filters' => [
                'search' => $request->input('search'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('stakeholders/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'receive_alerts' => 'boolean',
        ]);

        Stakeholder::create($validated);

        return redirect()->route('stakeholders.index')
            ->with('success', 'Stakeholder berhasil ditambahkan.');
    }

    public function edit(Stakeholder $stakeholder): Response
    {
        return Inertia::render('stakeholders/edit', [
            'stakeholder' => $stakeholder,
        ]);
    }

    public function update(Request $request, Stakeholder $stakeholder): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'receive_alerts' => 'boolean',
        ]);

        $stakeholder->update($validated);

        return redirect()->route('stakeholders.index')
            ->with('success', 'Stakeholder berhasil diperbarui.');
    }

    public function destroy(Stakeholder $stakeholder): RedirectResponse
    {
        // Check if stakeholder is being used in emissions
        $hasEmissions = $stakeholder->scope1Emissions()->exists()
            || $stakeholder->scope2Emissions()->exists()
            || $stakeholder->scope3Emissions()->exists();

        if ($hasEmissions) {
            return redirect()->route('stakeholders.index')
                ->with('error', 'Stakeholder tidak dapat dihapus karena masih digunakan dalam data emisi.');
        }

        $stakeholder->delete();

        return redirect()->route('stakeholders.index')
            ->with('success', 'Stakeholder berhasil dihapus.');
    }
}
