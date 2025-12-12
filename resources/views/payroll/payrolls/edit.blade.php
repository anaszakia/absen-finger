@extends('layouts.app')

@section('title', 'Edit Penggajian')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center mb-6">
            <a href="{{ route('payrolls.show', $payroll) }}" class="text-gray-600 hover:text-gray-800 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h2 class="text-2xl font-bold text-gray-800">Edit Penggajian</h2>
        </div>

        <form action="{{ route('payrolls.update', $payroll) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Karyawan</label>
                    <input type="text" value="{{ $payroll->employee->name ?? '-' }}" readonly
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                    <input type="text" value="{{ $payroll->period }}" readonly
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai - Selesai</label>
                    <input type="text" value="{{ $payroll->period_start->format('d/m/Y') }} - {{ $payroll->period_end->format('d/m/Y') }}" readonly
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                </div>
            </div>

            <div class="mt-6 mb-4 p-4 rounded bg-yellow-50 text-yellow-800 border border-yellow-200 flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>Untuk mengubah data gaji, silakan ubah master data komponen gaji, tunjangan, atau potongan, kemudian hapus dan buat ulang penggajian ini.
            </div>

            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                <textarea name="notes" id="notes" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('notes') border-red-500 @enderror"
                          placeholder="Keterangan tambahan (opsional)">{{ old('notes', $payroll->notes) }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-save mr-2"></i>Update
                </button>
                <a href="{{ route('payrolls.show', $payroll) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-times mr-2"></i>Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
