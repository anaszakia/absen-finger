@extends('layouts.app')

@section('title', 'Rekap Absensi')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Rekap Absensi</h2>
            <div class="flex gap-2">
                <button onclick="syncAfterReset()" id="btnSyncAfterReset" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fas fa-sync"></i>
                    Sync Setelah Reset
                </button>
                <button onclick="syncTodayData()" id="btnSyncToday" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fas fa-download"></i>
                    Sync Hari Ini
                </button>
            </div>
        </div>

        <!-- Sync Result -->
        <div id="syncResult" class="mb-4"></div>

        <!-- Filter Section -->
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <form method="GET" action="{{ route('attendances.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Karyawan</label>
                    <select name="employee_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Karyawan</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Attendance Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Out</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mesin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($attendances as $attendance)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $attendance->date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $attendance->employee->name }}</div>
                                <div class="text-sm text-gray-500">{{ $attendance->employee->employee_id }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $attendance->check_in ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $attendance->check_out ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'present' => 'bg-green-100 text-green-800',
                                        'late' => 'bg-yellow-100 text-yellow-800',
                                        'absent' => 'bg-red-100 text-red-800',
                                        'leave' => 'bg-blue-100 text-blue-800',
                                        'permission' => 'bg-purple-100 text-purple-800',
                                    ];
                                    $statusLabels = [
                                        'present' => 'Hadir',
                                        'late' => 'Terlambat',
                                        'absent' => 'Tidak Hadir',
                                        'leave' => 'Cuti',
                                        'permission' => 'Izin',
                                    ];
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$attendance->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusLabels[$attendance->status] ?? $attendance->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $attendance->attendanceMachine->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $attendance->notes ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                Tidak ada data absensi
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $attendances->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script>
function syncAfterReset() {
    const resultDiv = document.getElementById('syncResult');
    const btn = document.getElementById('btnSyncAfterReset');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';
    
    resultDiv.innerHTML = `
        <div class="bg-blue-50 border border-blue-300 rounded p-3">
            <i class="fas fa-spinner fa-spin mr-2"></i>Mengambil data karyawan BARU (filter ketat)...
        </div>
    `;
    
    fetch('{{ url("/api/fingerspot/sync-after-reset") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync"></i> Sync Setelah Reset';
        
        if (data.success) {
            const users = data.synced?.new_users || 0;
            const attendances = data.synced?.new_attendances || 0;
            const skipped = (data.filtered?.skipped_old_pins || 0) + (data.filtered?.skipped_old_data || 0);
            
            resultDiv.innerHTML = `
                <div class="bg-green-50 border border-green-300 rounded p-4">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    <strong>${data.message}</strong>
                    <div class="flex gap-2 mt-2">
                        <span class="bg-white px-3 py-1 rounded border">
                            <strong class="text-green-600">${users}</strong> karyawan baru
                        </span>
                        <span class="bg-white px-3 py-1 rounded border">
                            <strong class="text-blue-600">${attendances}</strong> absensi
                        </span>
                        <span class="bg-white px-3 py-1 rounded border">
                            <strong class="text-orange-600">${skipped}</strong> data lama di-skip
                        </span>
                    </div>
                    <button onclick="location.reload()" class="mt-3 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                        <i class="fas fa-sync-alt mr-1"></i>Refresh Halaman
                    </button>
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="bg-red-50 border border-red-300 rounded p-3">
                    <i class="fas fa-times-circle text-red-600 mr-2"></i>
                    <strong>Gagal:</strong> ${data.message}
                </div>
            `;
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync"></i> Sync Setelah Reset';
        resultDiv.innerHTML = `
            <div class="bg-red-50 border border-red-300 rounded p-3">
                <i class="fas fa-times-circle text-red-600 mr-2"></i>
                <strong>Error:</strong> ${error.message}
            </div>
        `;
    });
}

function syncTodayData() {
    const resultDiv = document.getElementById('syncResult');
    const btn = document.getElementById('btnSyncToday');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';
    
    resultDiv.innerHTML = `
        <div class="bg-blue-50 border border-blue-300 rounded p-3">
            <i class="fas fa-spinner fa-spin mr-2"></i>Mengambil data hari ini...
        </div>
    `;
    
    fetch('{{ url("/api/fingerspot/sync-today") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-download"></i> Sync Hari Ini';
        
        if (data.success) {
            const users = data.synced?.users || 0;
            const attendances = data.synced?.attendances || 0;
            
            resultDiv.innerHTML = `
                <div class="bg-green-50 border border-green-300 rounded p-4">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    <strong>${data.message}</strong>
                    <div class="flex gap-2 mt-2">
                        <span class="bg-white px-3 py-1 rounded border">
                            <strong class="text-blue-600">${users}</strong> karyawan
                        </span>
                        <span class="bg-white px-3 py-1 rounded border">
                            <strong class="text-green-600">${attendances}</strong> absensi
                        </span>
                    </div>
                    <button onclick="location.reload()" class="mt-3 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                        <i class="fas fa-sync-alt mr-1"></i>Refresh Halaman
                    </button>
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="bg-red-50 border border-red-300 rounded p-3">
                    <i class="fas fa-times-circle text-red-600 mr-2"></i>
                    <strong>Gagal:</strong> ${data.message}
                </div>
            `;
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-download"></i> Sync Hari Ini';
        resultDiv.innerHTML = `
            <div class="bg-red-50 border border-red-300 rounded p-3">
                <i class="fas fa-times-circle text-red-600 mr-2"></i>
                <strong>Error:</strong> ${error.message}
            </div>
        `;
    });
}
</script>

@endsection
