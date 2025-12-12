@extends('layouts.app')

@section('title', 'Slip Gaji - ' . $payroll->period)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4 print:hidden">
        <h2 class="text-2xl font-bold text-gray-800">Slip Gaji - {{ $payroll->period }}</h2>
        <div class="flex gap-2">
            <a href="{{ route('payrolls.index') }}" class="inline-flex items-center bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
            <button onclick="window.print()" class="inline-flex items-center bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg shadow-sm">
                <i class="fas fa-print mr-2"></i>Cetak
            </button>
        </div>
    </div>

    <!-- Employee Info -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <table class="w-full text-sm">
                    <tr>
                        <td class="font-semibold w-32">Nama</td>
                        <td>: {{ $payroll->employee->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold">NIK</td>
                        <td>: {{ $payroll->employee->employee_id ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold">Jabatan</td>
                        <td>: {{ $payroll->employee->position ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div>
                <table class="w-full text-sm">
                    <tr>
                        <td class="font-semibold w-32">Periode</td>
                        <td>: {{ $payroll->period }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold">Tanggal</td>
                        <td>: {{ $payroll->period_start->format('d/m/Y') }} - {{ $payroll->period_end->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold">Status</td>
                        <td>:
                            @if($payroll->status === 'draft')
                                <span class="inline-block px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800">Draft</span>
                            @elseif($payroll->status === 'approved')
                                <span class="inline-block px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">Approved</span>
                            @else
                                <span class="inline-block px-2 py-1 text-xs rounded bg-green-100 text-green-800">Paid</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Attendance Summary -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h4 class="text-lg font-semibold mb-4">Ringkasan Kehadiran</h4>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="text-blue-600 text-2xl mb-1"><i class="fas fa-calendar"></i></div>
                <div class="text-xs text-gray-500">Total Hari</div>
                <div class="text-lg font-bold">{{ $payroll->total_days }}</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <div class="text-green-600 text-2xl mb-1"><i class="fas fa-check"></i></div>
                <div class="text-xs text-gray-500">Hadir</div>
                <div class="text-lg font-bold">{{ $payroll->present_days }}</div>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4">
                <div class="text-yellow-600 text-2xl mb-1"><i class="fas fa-clock"></i></div>
                <div class="text-xs text-gray-500">Terlambat</div>
                <div class="text-lg font-bold">{{ $payroll->late_days }}</div>
            </div>
            <div class="bg-red-50 rounded-lg p-4">
                <div class="text-red-600 text-2xl mb-1"><i class="fas fa-times"></i></div>
                <div class="text-xs text-gray-500">Tidak Hadir</div>
                <div class="text-lg font-bold">{{ $payroll->absent_days }}</div>
            </div>
        </div>
    </div>

    <!-- Salary Details -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h4 class="text-lg font-semibold mb-4">Rincian Gaji</h4>
        <table class="min-w-full text-sm mb-4">
            <tbody>
                <!-- Basic Salary -->
                <tr>
                    <td class="py-2 font-semibold">Gaji Pokok</td>
                    <td class="py-2 text-right font-semibold">Rp {{ number_format($payroll->basic_salary, 0, ',', '.') }}</td>
                </tr>

                <!-- Allowances -->
                @php
                    $allowanceDetails = $payroll->details->where('type', 'allowance');
                @endphp
                @if($allowanceDetails->count() > 0)
                    <tr><td colspan="2" class="pt-4 pb-2 font-semibold text-gray-700">Tunjangan</td></tr>
                    @foreach($allowanceDetails as $detail)
                        <tr>
                            <td class="pl-6 py-1">{{ $detail->name }}</td>
                            <td class="py-1 text-right">Rp {{ number_format($detail->total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endif

                <!-- Gross Salary -->
                <tr class="bg-blue-50">
                    <td class="py-2 font-semibold">Gaji Kotor</td>
                    <td class="py-2 text-right font-semibold">Rp {{ number_format($payroll->gross_salary, 0, ',', '.') }}</td>
                </tr>

                <!-- Deductions -->
                @php
                    $deductionDetails = $payroll->details->where('type', 'deduction');
                @endphp
                @if($deductionDetails->count() > 0)
                    <tr><td colspan="2" class="pt-4 pb-2 font-semibold text-gray-700">Potongan</td></tr>
                    @foreach($deductionDetails as $detail)
                        <tr>
                            <td class="pl-6 py-1">
                                {{ $detail->name }}
                                @if($detail->quantity > 1)
                                    <small class="text-gray-500">({{ $detail->quantity }}x)</small>
                                @endif
                            </td>
                            <td class="py-1 text-right text-red-600">- Rp {{ number_format($detail->total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endif

                <!-- Net Salary -->
                <tr class="bg-green-50">
                    <td class="py-2 font-semibold">Gaji Bersih (Take Home Pay)</td>
                    <td class="py-2 text-right text-lg font-bold">Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        @if($payroll->notes)
            <div class="mb-2 p-3 rounded bg-blue-50 text-blue-800 border border-blue-200">
                <strong>Catatan:</strong> {{ $payroll->notes }}
            </div>
        @endif

        @if($payroll->payment_date)
            <div class="mb-2 p-3 rounded bg-green-50 text-green-800 border border-green-200">
                <strong>Tanggal Pembayaran:</strong> {{ $payroll->payment_date->format('d/m/Y') }}
            </div>
        @endif

        <div class="flex gap-2 mt-6 print:hidden">
            @if($payroll->status === 'draft')
                <form action="{{ route('payrolls.approve', $payroll) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg" onclick="return confirm('Approve penggajian ini?')">
                        <i class="fas fa-check mr-2"></i>Approve
                    </button>
                </form>
                <a href="{{ route('payrolls.edit', $payroll) }}" class="bg-yellow-400 hover:bg-yellow-500 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <form action="{{ route('payrolls.destroy', $payroll) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus penggajian ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-trash mr-2"></i>Hapus
                    </button>
                </form>
            @elseif($payroll->status === 'approved')
                <form action="{{ route('payrolls.pay', $payroll) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg" onclick="return confirm('Tandai sebagai sudah dibayar?')">
                        <i class="fas fa-money-bill mr-2"></i>Tandai Sebagai Dibayar
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<style>
@media print {
    .print\:hidden { display: none !important; }
    .sidebar, .main-header, .main-footer { display: none !important; }
    .content-wrapper { margin: 0 !important; padding: 0 !important; }
    body { background: #fff !important; }
}
</style>
@endsection
