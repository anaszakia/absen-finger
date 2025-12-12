<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DebugController extends Controller
{
    public function checkAttendance(Request $request)
    {
        $employeeId = $request->get('employee_id', 1);
        $periodStart = $request->get('period_start', now()->startOfMonth()->format('Y-m-d'));
        $periodEnd = $request->get('period_end', now()->endOfMonth()->format('Y-m-d'));

        $employee = Employee::find($employeeId);
        
        if (!$employee) {
            return response()->json([
                'error' => 'Employee not found',
                'employee_id' => $employeeId,
                'total_employees' => Employee::count(),
                'employees' => Employee::select('id', 'employee_id', 'name')->get()
            ]);
        }

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->orderBy('date', 'desc')
            ->get();

        $presentDays = $attendances->whereIn('status', ['present', 'late'])->count();
        $lateDays = $attendances->where('status', 'late')->count();
        $absentDays = $attendances->where('status', 'absent')->count();

        return response()->json([
            'employee' => [
                'id' => $employee->id,
                'employee_id' => $employee->employee_id,
                'name' => $employee->name,
                'basic_salary' => $employee->basic_salary
            ],
            'period' => [
                'start' => $periodStart,
                'end' => $periodEnd,
                'total_days' => Carbon::parse($periodStart)->diffInDays(Carbon::parse($periodEnd)) + 1
            ],
            'attendance_summary' => [
                'total_records' => $attendances->count(),
                'present_days' => $presentDays,
                'late_days' => $lateDays,
                'absent_days' => $absentDays,
            ],
            'status_breakdown' => [
                'present' => $attendances->where('status', 'present')->count(),
                'late' => $attendances->where('status', 'late')->count(),
                'absent' => $attendances->where('status', 'absent')->count(),
                'leave' => $attendances->where('status', 'leave')->count(),
                'permission' => $attendances->where('status', 'permission')->count(),
            ],
            'attendances' => $attendances->map(function($att) {
                return [
                    'date' => $att->date->format('Y-m-d'),
                    'check_in' => $att->check_in,
                    'check_out' => $att->check_out,
                    'status' => $att->status,
                ];
            }),
            'all_attendance_count' => Attendance::count(),
            'all_employees_with_attendance' => Attendance::distinct('employee_id')->count('employee_id')
        ]);
    }
}
