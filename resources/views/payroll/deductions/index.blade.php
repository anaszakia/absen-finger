@extends('layouts.app')

@section('title', 'Daftar Potongan Gaji')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
            <h2 class="text-2xl font-bold text-gray-800">Daftar Potongan Gaji</h2>
            <a href="{{ route('deductions.create') }}" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm">
                <i class="fas fa-plus mr-2"></i>Tambah Potongan
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">No</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Nama Potongan</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Tipe</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Nilai</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Auto Calculate</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($deductions as $index => $deduction)
                        <tr>
                            <td class="px-4 py-2">{{ $index + 1 }}</td>
                            <td class="px-4 py-2">{{ $deduction->name }}</td>
                            <td class="px-4 py-2">
                                @if($deduction->type === 'per_day')
                                    <span class="inline-block px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">Per Hari</span>
                                @elseif($deduction->type === 'per_hour')
                                    <span class="inline-block px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">Per Jam</span>
                                @elseif($deduction->type === 'fixed')
                                    <span class="inline-block px-2 py-1 text-xs rounded bg-indigo-100 text-indigo-800">Tetap</span>
                                @else
                                    <span class="inline-block px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800">Persentase</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                @if($deduction->type === 'percentage')
                                    {{ $deduction->percentage }}%
                                @else
                                    Rp {{ number_format($deduction->amount, 0, ',', '.') }}
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                @if($deduction->auto_calculate)
                                    <span class="inline-block px-2 py-1 text-xs rounded bg-green-100 text-green-800">Ya</span>
                                @else
                                    <span class="inline-block px-2 py-1 text-xs rounded bg-gray-200 text-gray-700">Tidak</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                @if($deduction->is_active)
                                    <span class="inline-block px-2 py-1 text-xs rounded bg-green-100 text-green-800">Aktif</span>
                                @else
                                    <span class="inline-block px-2 py-1 text-xs rounded bg-gray-200 text-gray-700">Tidak Aktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('deductions.edit', $deduction) }}" class="inline-flex items-center px-2 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded shadow-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('deductions.destroy', $deduction) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus potongan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-2 py-1 bg-red-600 hover:bg-red-700 text-white rounded shadow-sm" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500">Tidak ada data potongan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
