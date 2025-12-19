@extends('layouts.app')

@section('title', 'Data Karyawan')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Data Karyawan</h2>
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

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jabatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departemen</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($employees as $employee)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $employee->employee_id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($employee->photo)
                                        <img src="{{ asset('storage/' . $employee->photo) }}" alt="{{ $employee->name }}" class="w-10 h-10 rounded-full mr-3">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center mr-3">
                                            <i class="fas fa-user text-gray-600"></i>
                                        </div>
                                    @endif
                                    <div class="text-sm font-medium text-gray-900">{{ $employee->name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $employee->email ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $employee->position ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $employee->department ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($employee->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('employees.show', $employee) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('employees.edit', $employee) }}" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus karyawan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                Tidak ada data karyawan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $employees->links() }}
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