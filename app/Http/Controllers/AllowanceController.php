<?php

namespace App\Http\Controllers;

use App\Models\Allowance;
use Illuminate\Http\Request;

class AllowanceController extends Controller
{
    public function index()
    {
        $allowances = Allowance::orderBy('created_at', 'desc')->get();
        return view('payroll.allowances.index', compact('allowances'));
    }

    public function create()
    {
        return view('payroll.allowances.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:fixed,percentage',
            'amount' => 'nullable|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'requires_approval' => 'boolean',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['requires_approval'] = $request->has('requires_approval');
        $validated['amount'] = $validated['amount'] ?? 0;
        $validated['percentage'] = $validated['percentage'] ?? 0;

        Allowance::create($validated);

        return redirect()->route('allowances.index')
            ->with('success', 'Tunjangan berhasil ditambahkan');
    }

    public function edit(Allowance $allowance)
    {
        return view('payroll.allowances.edit', compact('allowance'));
    }

    public function update(Request $request, Allowance $allowance)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:fixed,percentage',
            'amount' => 'nullable|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'requires_approval' => 'boolean',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['requires_approval'] = $request->has('requires_approval');
        $validated['amount'] = $validated['amount'] ?? 0;
        $validated['percentage'] = $validated['percentage'] ?? 0;

        $allowance->update($validated);

        return redirect()->route('allowances.index')
            ->with('success', 'Tunjangan berhasil diupdate');
    }

    public function destroy(Allowance $allowance)
    {
        $allowance->delete();

        return redirect()->route('allowances.index')
            ->with('success', 'Tunjangan berhasil dihapus');
    }
}
