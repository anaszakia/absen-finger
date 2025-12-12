@extends('layouts.app')

@section('title', 'Tambah Komponen Gaji')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center mb-6">
            <a href="{{ route('salary-components.index') }}" class="text-gray-600 hover:text-gray-800 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h2 class="text-2xl font-bold text-gray-800">Tambah Komponen Gaji</h2>
        </div>

        <form action="{{ route('salary-components.store') }}" method="POST" id="componentForm">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Komponen *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                           placeholder="Contoh: Tunjangan Jabatan">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe *</label>
                    <select name="type" id="type" required onchange="toggleInputs()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('type') border-red-500 @enderror">
                        <option value="">Pilih Tipe</option>
                        <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Nominal Tetap</option>
                        <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Persentase dari Gaji Pokok</option>
                    </select>
                    @error('type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div id="amount-field" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nominal (Rp) *</label>
                    <input type="number" name="amount" value="{{ old('amount') }}" min="0" step="1"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('amount') border-red-500 @enderror"
                           placeholder="Contoh: 500000">
                    @error('amount')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div id="percentage-field" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Persentase (%) *</label>
                    <input type="number" name="percentage" value="{{ old('percentage') }}" min="0" max="100" step="0.01"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('percentage') border-red-500 @enderror"
                           placeholder="Contoh: 10">
                    @error('percentage')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="description" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                              placeholder="Keterangan tambahan (opsional)">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Aktif</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
                <a href="{{ route('salary-components.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-times mr-2"></i>Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleInputs() {
    const type = document.getElementById('type').value;
    const amountField = document.getElementById('amount-field');
    const percentageField = document.getElementById('percentage-field');
    
    if (type === 'fixed') {
        amountField.style.display = 'block';
        percentageField.style.display = 'none';
        amountField.querySelector('input').required = true;
        percentageField.querySelector('input').required = false;
    } else if (type === 'percentage') {
        amountField.style.display = 'none';
        percentageField.style.display = 'block';
        amountField.querySelector('input').required = false;
        percentageField.querySelector('input').required = true;
    } else {
        amountField.style.display = 'none';
        percentageField.style.display = 'none';
    }
}

// Run on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleInputs();
});
</script>
@endsection
