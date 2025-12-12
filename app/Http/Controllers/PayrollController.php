<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\Employee;
use App\Models\SalaryComponent;
use App\Models\Deduction;
use App\Models\Allowance;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $query = Payroll::with('employee');

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payrolls = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('payroll.payrolls.index', compact('payrolls'));
    }

    public function history(Request $request)
    {
        // Group payrolls by period with summary data
        $periodsQuery = Payroll::selectRaw('
                period,
                MIN(period_start) as period_start,
                MAX(period_end) as period_end,
                COUNT(*) as total_employees,
                COUNT(CASE WHEN status = "draft" THEN 1 END) as draft_count,
                COUNT(CASE WHEN status = "approved" THEN 1 END) as approved_count,
                COUNT(CASE WHEN status = "paid" THEN 1 END) as paid_count,
                SUM(basic_salary) as total_basic_salary,
                SUM(total_allowances) as total_allowances,
                SUM(total_deductions) as total_deductions,
                SUM(gross_salary) as total_gross_salary,
                SUM(net_salary) as total_net_salary,
                MAX(created_at) as latest_created_at
            ')
            ->groupBy('period')
            ->orderBy('period', 'desc');

        // Filter by year if provided
        if ($request->filled('year')) {
            $periodsQuery->where('period', 'LIKE', $request->year . '%');
        }

        $periods = $periodsQuery->paginate(12);
        
        // Get available years for filter
        $years = Payroll::selectRaw('DISTINCT SUBSTRING(period, 1, 4) as year')
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        return view('payroll.payrolls.history', compact('periods', 'years'));
    }

    public function create()
    {
        $employees = Employee::all();
        return view('payroll.payrolls.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period' => 'required|string',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'notes' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $periodStart = Carbon::parse($validated['period_start']);
        $periodEnd = Carbon::parse($validated['period_end']);

        // Hitung total hari dalam periode
        $totalDays = $periodStart->diffInDays($periodEnd) + 1;

        // Hitung kehadiran
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->get();

        $totalRecords = $attendances->count();
        $presentDays = $attendances->whereIn('status', ['present', 'late'])->count();
        $lateDays = $attendances->where('status', 'late')->count();
        $recordedAbsent = $attendances->where('status', 'absent')->count();
        $unrecordedDays = $totalDays - $totalRecords;
        $absentDays = $recordedAbsent + $unrecordedDays;

        // Buat payroll
        $payroll = Payroll::create([
            'employee_id' => $employee->id,
            'period' => $validated['period'],
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'basic_salary' => $employee->basic_salary ?? 0,
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'late_days' => $lateDays,
            'absent_days' => $absentDays,
            'total_allowances' => 0,
            'total_deductions' => 0,
            'gross_salary' => 0,
            'net_salary' => 0,
            'status' => 'draft',
            'notes' => $validated['notes'],
        ]);

        $this->calculatePayroll($payroll);

        return redirect()->route('payrolls.show', $payroll)
            ->with('success', 'Penggajian berhasil dibuat');
    }

    public function show(Payroll $payroll)
    {
        $payroll->load(['employee', 'details']);
        return view('payroll.payrolls.show', compact('payroll'));
    }

    public function edit(Payroll $payroll)
    {
        if ($payroll->status !== 'draft') {
            return redirect()->route('payrolls.show', $payroll)
                ->with('error', 'Hanya penggajian dengan status draft yang bisa diedit');
        }

        $employees = Employee::all();
        return view('payroll.payrolls.edit', compact('payroll', 'employees'));
    }

    public function update(Request $request, Payroll $payroll)
    {
        if ($payroll->status !== 'draft') {
            return redirect()->route('payrolls.show', $payroll)
                ->with('error', 'Hanya penggajian dengan status draft yang bisa diupdate');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $payroll->update($validated);
        $this->calculatePayroll($payroll);

        return redirect()->route('payrolls.show', $payroll)
            ->with('success', 'Penggajian berhasil diupdate');
    }

    public function destroy(Payroll $payroll)
    {
        if ($payroll->status !== 'draft') {
            return redirect()->route('payrolls.index')
                ->with('error', 'Hanya penggajian dengan status draft yang bisa dihapus');
        }

        $payroll->delete();

        return redirect()->route('payrolls.index')
            ->with('success', 'Penggajian berhasil dihapus');
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|string',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        $periodStart = Carbon::parse($validated['period_start']);
        $periodEnd = Carbon::parse($validated['period_end']);
        $employees = Employee::all();
        $generated = 0;

        foreach ($employees as $employee) {
            // Skip jika sudah ada payroll untuk periode ini
            $exists = Payroll::where('employee_id', $employee->id)
                ->where('period', $validated['period'])
                ->exists();

            if ($exists) {
                continue;
            }

            // Hitung total hari dalam periode
            $totalDays = $periodStart->diffInDays($periodEnd) + 1;

            // Hitung kehadiran
            $attendances = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$periodStart, $periodEnd])
                ->get();

            $totalRecords = $attendances->count();
            $presentDays = $attendances->whereIn('status', ['present', 'late'])->count();
            $lateDays = $attendances->where('status', 'late')->count();
            $recordedAbsent = $attendances->where('status', 'absent')->count();
            $unrecordedDays = $totalDays - $totalRecords;
            $absentDays = $recordedAbsent + $unrecordedDays;

            // Buat payroll
            $payroll = Payroll::create([
                'employee_id' => $employee->id,
                'period' => $validated['period'],
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'basic_salary' => $employee->basic_salary ?? 0,
                'total_days' => $totalDays,
                'present_days' => $presentDays,
                'late_days' => $lateDays,
                'absent_days' => $absentDays,
                'total_allowances' => 0,
                'total_deductions' => 0,
                'gross_salary' => 0,
                'net_salary' => 0,
                'status' => 'draft',
            ]);

            $this->calculatePayroll($payroll);
            $generated++;
        }

        return redirect()->route('payrolls.index')
            ->with('success', "Berhasil generate {$generated} penggajian");
    }

    public function approve(Payroll $payroll)
    {
        if ($payroll->status !== 'draft') {
            return redirect()->route('payrolls.show', $payroll)
                ->with('error', 'Hanya penggajian dengan status draft yang bisa diapprove');
        }

        $payroll->update(['status' => 'approved']);

        return redirect()->route('payrolls.show', $payroll)
            ->with('success', 'Penggajian berhasil diapprove');
    }

    public function recalculate(Payroll $payroll)
    {
        if ($payroll->status !== 'draft') {
            return redirect()->route('payrolls.show', $payroll)
                ->with('error', 'Hanya penggajian dengan status draft yang bisa dihitung ulang');
        }

        $employee = $payroll->employee;
        $periodStart = $payroll->period_start;
        $periodEnd = $payroll->period_end;

        // Hitung total hari dalam periode
        $totalDays = $periodStart->diffInDays($periodEnd) + 1;

        // Hitung kehadiran
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->get();

        $totalRecords = $attendances->count();
        $presentDays = $attendances->whereIn('status', ['present', 'late'])->count();
        $lateDays = $attendances->where('status', 'late')->count();
        $recordedAbsent = $attendances->where('status', 'absent')->count();
        $unrecordedDays = $totalDays - $totalRecords;
        $absentDays = $recordedAbsent + $unrecordedDays;

        // Update data kehadiran
        $payroll->update([
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'late_days' => $lateDays,
            'absent_days' => $absentDays,
        ]);

        // Hitung ulang gaji
        $this->calculatePayroll($payroll);

        return redirect()->route('payrolls.show', $payroll)
            ->with('success', 'Penggajian berhasil dihitung ulang dengan data attendance terbaru');
    }

    public function pay(Payroll $payroll)
    {
        if ($payroll->status !== 'approved') {
            return redirect()->route('payrolls.show', $payroll)
                ->with('error', 'Hanya penggajian dengan status approved yang bisa dibayar');
        }

        $payroll->update([
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        return redirect()->route('payrolls.show', $payroll)
            ->with('success', 'Penggajian berhasil dibayar');
    }

    private function calculatePayroll(Payroll $payroll)
    {
        // Hapus detail lama
        $payroll->details()->delete();

        $totalAllowances = 0;
        $totalDeductions = 0;
        $basicSalary = $payroll->basic_salary;

        // Tambahkan komponen gaji aktif
        $components = SalaryComponent::where('is_active', true)->get();
        foreach ($components as $component) {
            if ($component->type === 'fixed') {
                $amount = $component->amount;
            } else {
                $amount = ($basicSalary * $component->percentage) / 100;
            }

            PayrollDetail::create([
                'payroll_id' => $payroll->id,
                'type' => 'allowance',
                'name' => $component->name,
                'calculation_type' => $component->type,
                'amount' => $component->type === 'fixed' ? $component->amount : $component->percentage,
                'quantity' => 1,
                'total' => $amount,
            ]);

            $totalAllowances += $amount;
        }

        // Tambahkan tunjangan aktif
        $allowances = Allowance::where('is_active', true)->get();
        foreach ($allowances as $allowance) {
            if ($allowance->type === 'fixed') {
                $amount = $allowance->amount;
            } else {
                $amount = ($basicSalary * $allowance->percentage) / 100;
            }

            PayrollDetail::create([
                'payroll_id' => $payroll->id,
                'type' => 'allowance',
                'name' => $allowance->name,
                'calculation_type' => $allowance->type,
                'amount' => $allowance->type === 'fixed' ? $allowance->amount : $allowance->percentage,
                'quantity' => 1,
                'total' => $amount,
            ]);

            $totalAllowances += $amount;
        }

        // Hitung potongan
        $deductions = Deduction::where('is_active', true)->get();
        foreach ($deductions as $deduction) {
            $quantity = 1;
            $amount = 0;

            if ($deduction->auto_calculate) {
                if ($deduction->type === 'per_day') {
                    $quantity = $payroll->absent_days + $payroll->late_days;
                    $amount = $deduction->amount * $quantity;
                } elseif ($deduction->type === 'per_hour') {
                    // Untuk per_hour, bisa dikembangkan lebih lanjut
                    $quantity = 0;
                    $amount = 0;
                }
            } else {
                if ($deduction->type === 'fixed') {
                    $amount = $deduction->amount;
                } elseif ($deduction->type === 'percentage') {
                    $amount = ($basicSalary * $deduction->percentage) / 100;
                }
            }

            if ($amount > 0) {
                PayrollDetail::create([
                    'payroll_id' => $payroll->id,
                    'type' => 'deduction',
                    'name' => $deduction->name,
                    'calculation_type' => $deduction->type,
                    'amount' => $deduction->type === 'fixed' ? $deduction->amount : $deduction->percentage,
                    'quantity' => $quantity,
                    'total' => $amount,
                ]);

                $totalDeductions += $amount;
            }
        }

        // Update total
        $grossSalary = $basicSalary + $totalAllowances;
        $netSalary = $grossSalary - $totalDeductions;

        $payroll->update([
            'total_allowances' => $totalAllowances,
            'total_deductions' => $totalDeductions,
            'gross_salary' => $grossSalary,
            'net_salary' => $netSalary,
        ]);
    }
}
