<?php

namespace App\Http\Controllers;

use App\Models\SalaryComponent;
use Illuminate\Http\Request;

class SalaryComponentController extends Controller
{
    public function index()
    {
        $components = SalaryComponent::orderBy('created_at', 'desc')->get();
        return view('payroll.salary-components.index', compact('components'));
    }

    public function create()
    {
        return view('payroll.salary-components.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:fixed,percentage',
            'amount' => 'required_if:type,fixed|nullable|numeric|min:0',
            'percentage' => 'required_if:type,percentage|nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['amount'] = $validated['amount'] ?? 0;
        $validated['percentage'] = $validated['percentage'] ?? 0;

        SalaryComponent::create($validated);

        return redirect()->route('salary-components.index')
            ->with('success', 'Komponen gaji berhasil ditambahkan');
    }

    public function edit(SalaryComponent $salaryComponent)
    {
        return view('payroll.salary-components.edit', compact('salaryComponent'));
    }

    public function update(Request $request, SalaryComponent $salaryComponent)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:fixed,percentage',
            'amount' => 'required_if:type,fixed|nullable|numeric|min:0',
            'percentage' => 'required_if:type,percentage|nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['amount'] = $validated['amount'] ?? 0;
        $validated['percentage'] = $validated['percentage'] ?? 0;

        $salaryComponent->update($validated);

        return redirect()->route('salary-components.index')
            ->with('success', 'Komponen gaji berhasil diupdate');
    }

    public function destroy(SalaryComponent $salaryComponent)
    {
        $salaryComponent->delete();

        return redirect()->route('salary-components.index')
            ->with('success', 'Komponen gaji berhasil dihapus');
    }
}
