@extends('layouts.app')

@section('title', 'Detail Karyawan')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Detail Karyawan</h2>
                <div class="flex gap-2">
                    <a href="{{ route('employees.edit', $employee) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    <a href="{{ route('employees.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1 text-center">
                    @if($employee->photo)
                        <img src="{{ asset('storage/' . $employee->photo) }}" alt="{{ $employee->name }}" class="w-48 h-48 object-cover rounded-lg mx-auto mb-4">
                    @else
                        <div class="w-48 h-48 rounded-lg bg-gray-300 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user text-gray-600 text-6xl"></i>
                        </div>
                    @endif
                    
                    @if($employee->is_active)
                        <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            Aktif
                        </span>
                    @else
                        <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            Tidak Aktif
                        </span>
                    @endif
                </div>

                <div class="md:col-span-2">
                    <table class="w-full">
                        <tr class="border-b">
                            <td class="py-3 font-semibold text-gray-700 w-1/3">ID Karyawan</td>
                            <td class="py-3 text-gray-600">{{ $employee->employee_id }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-3 font-semibold text-gray-700">Nama Lengkap</td>
                            <td class="py-3 text-gray-600">{{ $employee->name }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-3 font-semibold text-gray-700">Email</td>
                            <td class="py-3 text-gray-600">{{ $employee->email ?? '-' }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-3 font-semibold text-gray-700">No. Telepon</td>
                            <td class="py-3 text-gray-600">{{ $employee->phone ?? '-' }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-3 font-semibold text-gray-700">Jabatan</td>
                            <td class="py-3 text-gray-600">{{ $employee->position ?? '-' }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-3 font-semibold text-gray-700">Departemen</td>
                            <td class="py-3 text-gray-600">{{ $employee->department ?? '-' }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-3 font-semibold text-gray-700">Tanggal Bergabung</td>
                            <td class="py-3 text-gray-600">{{ $employee->join_date?->format('d/m/Y') ?? '-' }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-3 font-semibold text-gray-700">Gaji Pokok</td>
                            <td class="py-3 text-gray-600">Rp {{ number_format($employee->basic_salary, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-3 font-semibold text-gray-700">Alamat</td>
                            <td class="py-3 text-gray-600">{{ $employee->address ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Riwayat Absensi (10 Terakhir)</h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($employee->attendances->take(10) as $attendance)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $attendance->date->format('d/m/Y') }}
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                    Belum ada data absensi
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
