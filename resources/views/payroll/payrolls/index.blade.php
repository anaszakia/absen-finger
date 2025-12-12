@extends('layouts.app')

@section('title', 'Daftar Penggajian')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
            <h2 class="text-2xl font-bold text-gray-800">Daftar Penggajian</h2>
            <div class="flex gap-2">
                <a href="{{ route('payrolls.create') }}" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm">
                    <i class="fas fa-plus mr-2"></i>Buat Manual
                </a>
                <button type="button" class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow-sm" data-toggle="modal" data-target="#generateModal">
                    <i class="fas fa-cogs mr-2"></i>Generate Otomatis
                </button>
            </div>
        </div>

        <!-- Filter -->
        <form method="GET" action="{{ route('payrolls.index') }}" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" name="period" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Periode (contoh: 2025-01)" value="{{ request('period') }}">
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <a href="{{ route('payrolls.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                    <i class="fas fa-redo mr-2"></i>Reset
                </a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">No</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Periode</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Karyawan</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Gaji Pokok</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Tunjangan</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Potongan</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Gaji Bersih</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($payrolls as $index => $payroll)
                        <tr>
                            <td class="px-4 py-2">{{ $payrolls->firstItem() + $index }}</td>
                            <td class="px-4 py-2">{{ $payroll->period }}</td>
                            <td class="px-4 py-2">{{ $payroll->employee->name ?? '-' }}</td>
                            <td class="px-4 py-2">Rp {{ number_format($payroll->basic_salary, 0, ',', '.') }}</td>
                            <td class="px-4 py-2">Rp {{ number_format($payroll->total_allowances, 0, ',', '.') }}</td>
                            <td class="px-4 py-2">Rp {{ number_format($payroll->total_deductions, 0, ',', '.') }}</td>
                            <td class="px-4 py-2 font-bold">Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</td>
                            <td class="px-4 py-2">
                                @if($payroll->status === 'draft')
                                    <span class="inline-block px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800">Draft</span>
                                @elseif($payroll->status === 'approved')
                                    <span class="inline-block px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">Approved</span>
                                @else
                                    <span class="inline-block px-2 py-1 text-xs rounded bg-green-100 text-green-800">Paid</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-center">
                                <div class="flex items-center justify-center gap-2 flex-wrap">
                                    <a href="{{ route('payrolls.show', $payroll) }}" class="inline-flex items-center px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded shadow-sm" title="Lihat">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($payroll->status === 'draft')
                                        <a href="{{ route('payrolls.edit', $payroll) }}" class="inline-flex items-center px-2 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded shadow-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('payrolls.approve', $payroll) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-2 py-1 bg-green-600 hover:bg-green-700 text-white rounded shadow-sm" title="Approve" onclick="return confirm('Approve penggajian ini?')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('payrolls.destroy', $payroll) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus penggajian ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-2 py-1 bg-red-600 hover:bg-red-700 text-white rounded shadow-sm" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @elseif($payroll->status === 'approved')
                                        <form action="{{ route('payrolls.pay', $payroll) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-2 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded shadow-sm" title="Bayar" onclick="return confirm('Tandai sebagai sudah dibayar?')">
                                                <i class="fas fa-money-bill"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-gray-500">Tidak ada data penggajian</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $payrolls->links() }}
        </div>
    </div>
</div>

<!-- Modal Generate -->
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('payrolls.generate') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Generate Penggajian Otomatis</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label for="period" class="block text-sm font-medium text-gray-700 mb-1">Periode <span class="text-red-500">*</span></label>
                        <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="period" name="period" placeholder="Contoh: 2025-01" required>
                        <small class="text-gray-500">Format: YYYY-MM</small>
                    </div>
                    <div class="mb-4">
                        <label for="period_start" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="period_start" name="period_start" required>
                    </div>
                    <div class="mb-4">
                        <label for="period_end" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                        <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="period_end" name="period_end" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg" data-dismiss="modal">Batal</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
