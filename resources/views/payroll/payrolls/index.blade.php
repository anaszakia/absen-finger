@extends('layouts.app')

@section('title', 'Daftar Penggajian')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-3xl font-bold text-gray-800">Penggajian Karyawan</h2>
            <a href="{{ route('payrolls.history') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                <i class="fas fa-history mr-1"></i>Lihat Riwayat Periode
            </a>
        </div>
        <p class="text-gray-600">Kelola penggajian karyawan per periode</p>
    </div>

    <!-- Generate Form - Simple & Direct -->
    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-cogs text-green-600 mr-2"></i>Generate Penggajian Periode Baru
        </h3>
        <form action="{{ route('payrolls.generate') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Periode</label>
                    <input type="text" name="period" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="2025-01" required>
                    <small class="text-gray-500 text-xs">Format: YYYY-MM</small>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                    <input type="date" name="period_start" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                    <input type="date" name="period_end" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" required>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition">
                        <i class="fas fa-play mr-2"></i>Generate Semua Karyawan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow-md">
        <!-- Filter & Search Bar -->
        <div class="p-4 border-b border-gray-200 bg-gray-50">
            <form method="GET" action="{{ route('payrolls.index') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Cari Periode</label>
                    <input type="text" name="period" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500" placeholder="2025-01" value="{{ request('period') }}">
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    <i class="fas fa-search mr-1"></i>Cari
                </button>
                @if(request()->anyFilled(['period', 'status']))
                    <a href="{{ route('payrolls.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-times mr-1"></i>Reset
                    </a>
                @endif
            </form>
        </div>


        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Periode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Karyawan</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Gaji Pokok</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Tunjangan</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Potongan</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Gaji Bersih</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($payrolls as $payroll)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                {{ $payroll->period }}
                                <div class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($payroll->period_start)->format('d/m') }} - {{ \Carbon\Carbon::parse($payroll->period_end)->format('d/m/Y') }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $payroll->employee->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $payroll->employee->employee_id ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">Rp {{ number_format($payroll->basic_salary, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-sm text-green-600">+{{ number_format($payroll->total_allowances, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-sm text-red-600">-{{ number_format($payroll->total_deductions, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-sm font-bold text-gray-900">Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($payroll->status === 'draft')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-edit mr-1"></i>Draft
                                    </span>
                                @elseif($payroll->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-check-circle mr-1"></i>Approved
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-double mr-1"></i>Paid
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Always show detail -->
                                    <a href="{{ route('payrolls.show', $payroll) }}" class="inline-flex items-center px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded text-xs font-medium transition" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($payroll->status === 'draft')
                                        <!-- Draft: Edit, Approve, Delete -->
                                        <a href="{{ route('payrolls.edit', $payroll) }}" class="inline-flex items-center px-2 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded text-xs font-medium transition" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('payrolls.approve', $payroll) }}" method="POST" class="inline-block" onsubmit="return confirm('Approve penggajian ini?')">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-2 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-medium transition" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('payrolls.destroy', $payroll) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-2 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-medium transition" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @elseif($payroll->status === 'approved')
                                        <!-- Approved: Pay -->
                                        <form action="{{ route('payrolls.pay', $payroll) }}" method="POST" class="inline-block" onsubmit="return confirm('Tandai sebagai sudah dibayar?')">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-2 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs font-medium transition" title="Bayar">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </button>
                                        </form>
                                    @else
                                        <!-- Paid: No action -->
                                        <span class="text-xs text-gray-400 italic">Selesai</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <i class="fas fa-inbox text-5xl mb-3"></i>
                                    <p class="text-lg font-medium">Belum ada data penggajian</p>
                                    <p class="text-sm mt-1">Generate penggajian menggunakan form di atas</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($payrolls->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $payrolls->links() }}
        </div>
        @endif
    </div>

    <!-- Info Box -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-3"></i>
            <div class="text-sm text-gray-700">
                <p class="font-semibold text-gray-800 mb-1">Alur Penggajian:</p>
                <ol class="list-decimal list-inside space-y-1 text-xs">
                    <li><strong>Generate:</strong> Buat penggajian otomatis untuk semua karyawan (status: Draft)</li>
                    <li><strong>Review:</strong> Periksa detail dan hitung ulang jika perlu</li>
                    <li><strong>Approve:</strong> Setujui penggajian (status: Approved)</li>
                    <li><strong>Bayar:</strong> Tandai sebagai sudah dibayar (status: Paid)</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection
