@extends('layouts.app')

@section('title', 'Rekap Absensi')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Rekap Absensi</h2>
            <div class="flex gap-2">
                <a href="{{ route('attendances.import') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fas fa-file-upload"></i>
                    Import dari USB
                </a>
                <button onclick="checkFingerspotData()" id="btnCheckFingerspot" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fas fa-sync"></i>
                    Cek Data Fingerspot
                </button>
            </div>
        </div>
        
        <!-- Fingerspot Data Result -->
        <div id="fingerspotResult" class="mb-4"></div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

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
function checkFingerspotData() {
    const resultDiv = document.getElementById('fingerspotResult');
    const btn = document.getElementById('btnCheckFingerspot');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
    
    resultDiv.innerHTML = `
        <div class="bg-blue-50 border border-blue-300 rounded p-4">
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Mengecek data dari Fingerspot.io...
        </div>
    `;
    
    fetch('{{ route("fingerspot.sync-users") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync"></i> Cek Data Fingerspot';
        
        if (data.existing_data) {
            const emp = data.existing_data.total_employees;
            const att = data.existing_data.recent_attendances;
            
            resultDiv.innerHTML = `
                <div class="bg-green-50 border border-green-300 rounded p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="font-semibold text-green-800 mb-3">
                                <i class="fas fa-check-circle mr-2"></i>
                                Status Data Fingerspot.io
                            </h4>
                            
                            <div class="grid grid-cols-2 gap-4 mb-3">
                                <div class="bg-white p-3 rounded shadow-sm">
                                    <p class="text-2xl font-bold text-blue-600">${emp}</p>
                                    <p class="text-sm text-gray-600">Total Karyawan Terdaftar</p>
                                </div>
                                <div class="bg-white p-3 rounded shadow-sm">
                                    <p class="text-2xl font-bold text-green-600">${att}</p>
                                    <p class="text-sm text-gray-600">Absensi Hari Ini</p>
                                </div>
                            </div>
                            
                            <div class="bg-white border border-green-200 rounded p-3">
                                <p class="text-sm text-gray-700 mb-2">
                                    <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                                    Data karyawan & absensi otomatis tersinkronisasi dari mesin via webhook.
                                </p>
                                <p class="text-sm text-gray-600">
                                    Untuk menambah data: Minta karyawan scan jari di mesin atau buka 
                                    <a href="{{ route('machines.fingerspot-setup') }}" class="text-blue-600 hover:underline">Halaman Setup</a>
                                </p>
                            </div>
                        </div>
                        <button onclick="document.getElementById('fingerspotResult').innerHTML=''" class="ml-4 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="bg-yellow-50 border border-yellow-300 rounded p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="font-semibold text-yellow-800 mb-2">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                ${data.message || 'Tidak ada data dari Fingerspot'}
                            </h4>
                            <p class="text-sm text-gray-700 mb-3">
                                Belum ada data karyawan atau absensi yang masuk dari mesin Fingerspot.
                            </p>
                            <div class="bg-white border border-yellow-200 rounded p-3">
                                <p class="text-sm font-semibold mb-2">Cara Mendapatkan Data:</p>
                                <ol class="text-sm text-gray-700 space-y-1 ml-4 list-decimal">
                                    <li><strong>Paling Cepat:</strong> Minta karyawan scan jari di mesin → Data otomatis masuk</li>
                                    <li>Pastikan webhook sudah dikonfigurasi di <a href="https://developer.fingerspot.io" target="_blank" class="text-blue-600 hover:underline">developer.fingerspot.io</a></li>
                                    <li>Atau buka <a href="{{ route('machines.fingerspot-setup') }}" class="text-blue-600 hover:underline">Halaman Setup Fingerspot</a> untuk panduan lengkap</li>
                                </ol>
                            </div>
                        </div>
                        <button onclick="document.getElementById('fingerspotResult').innerHTML=''" class="ml-4 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync"></i> Cek Data Fingerspot';
        
        resultDiv.innerHTML = `
            <div class="bg-red-50 border border-red-300 rounded p-4">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h4 class="font-semibold text-red-800 mb-2">
                            <i class="fas fa-times-circle mr-2"></i>
                            Gagal Mengecek Data
                        </h4>
                        <p class="text-sm text-gray-700">${error.message}</p>
                        <p class="text-sm text-gray-600 mt-2">
                            Cek konfigurasi di <a href="{{ route('machines.fingerspot-setup') }}" class="text-blue-600 hover:underline">Halaman Setup</a>
                        </p>
                    </div>
                    <button onclick="document.getElementById('fingerspotResult').innerHTML=''" class="ml-4 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
    });
}
</script>
@endsection
