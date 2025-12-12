<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use Illuminate\Http\Request;

class DeductionController extends Controller
{
    public function index()
    {
        $deductions = Deduction::orderBy('created_at', 'desc')->get();
        return view('payroll.deductions.index', compact('deductions'));
    }

    public function create()
    {
        return view('payroll.deductions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:per_day,per_hour,fixed,percentage',
            'amount' => 'nullable|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'auto_calculate' => 'boolean',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['auto_calculate'] = $request->has('auto_calculate');
        $validated['amount'] = $validated['amount'] ?? 0;
        $validated['percentage'] = $validated['percentage'] ?? 0;

        Deduction::create($validated);

        return redirect()->route('deductions.index')
            ->with('success', 'Potongan berhasil ditambahkan');
    }

    public function edit(Deduction $deduction)
    {
        return view('payroll.deductions.edit', compact('deduction'));
    }

    public function update(Request $request, Deduction $deduction)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:per_day,per_hour,fixed,percentage',
            'amount' => 'nullable|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'auto_calculate' => 'boolean',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['auto_calculate'] = $request->has('auto_calculate');
        $validated['amount'] = $validated['amount'] ?? 0;
        $validated['percentage'] = $validated['percentage'] ?? 0;

        $deduction->update($validated);

        return redirect()->route('deductions.index')
            ->with('success', 'Potongan berhasil diupdate');
    }

    public function destroy(Deduction $deduction)
    {
        $deduction->delete();

        return redirect()->route('deductions.index')
            ->with('success', 'Potongan berhasil dihapus');
    }
}
