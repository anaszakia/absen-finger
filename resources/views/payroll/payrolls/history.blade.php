@extends('layouts.app')

@section('title', 'Riwayat Penggajian')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
            <h2 class="text-2xl font-bold text-gray-800">Riwayat Penggajian</h2>
            <a href="{{ route('payrolls.index') }}" class="inline-flex items-center bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg shadow-sm">
                <i class="fas fa-list mr-2"></i>Lihat Semua Slip Gaji
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800 border border-green-200 flex items-center">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Filter -->
        <form method="GET" action="{{ route('payrolls.history') }}" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <select name="year" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Tahun</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <a href="{{ route('payrolls.history') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                    <i class="fas fa-redo mr-2"></i>Reset
                </a>
            </div>
        </form>

        <!-- Period Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($periods as $period)
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-800">{{ $period->period }}</h3>
                            <span class="inline-block px-3 py-1 text-xs rounded bg-blue-100 text-blue-800">
                                {{ $period->total_employees }} Karyawan
                            </span>
                        </div>

                        <div class="mb-4 text-sm text-gray-600">
                            <i class="fas fa-calendar mr-1"></i>
                            {{ \Carbon\Carbon::parse($period->period_start)->format('d M Y') }} - 
                            {{ \Carbon\Carbon::parse($period->period_end)->format('d M Y') }}
                        </div>

                        <!-- Status Summary -->
                        <div class="grid grid-cols-3 gap-2 mb-4">
                            <div class="text-center p-2 bg-yellow-50 rounded">
                                <div class="text-xs text-gray-600">Draft</div>
                                <div class="text-lg font-bold text-yellow-700">{{ $period->draft_count }}</div>
                            </div>
                            <div class="text-center p-2 bg-blue-50 rounded">
                                <div class="text-xs text-gray-600">Approved</div>
                                <div class="text-lg font-bold text-blue-700">{{ $period->approved_count }}</div>
                            </div>
                            <div class="text-center p-2 bg-green-50 rounded">
                                <div class="text-xs text-gray-600">Paid</div>
                                <div class="text-lg font-bold text-green-700">{{ $period->paid_count }}</div>
                            </div>
                        </div>

                        <!-- Financial Summary -->
                        <div class="border-t pt-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Total Gaji Pokok:</span>
                                <span class="font-semibold">Rp {{ number_format($period->total_basic_salary, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Total Tunjangan:</span>
                                <span class="font-semibold text-green-600">+ Rp {{ number_format($period->total_allowances, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Total Potongan:</span>
                                <span class="font-semibold text-red-600">- Rp {{ number_format($period->total_deductions, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm border-t pt-2">
                                <span class="text-gray-800 font-semibold">Total Gaji Bersih:</span>
                                <span class="font-bold text-blue-700">Rp {{ number_format($period->total_net_salary, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="mt-4">
                            <a href="{{ route('payrolls.index', ['period' => $period->period]) }}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                                <i class="fas fa-eye mr-2"></i>Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-12">
                    <i class="fas fa-folder-open text-gray-300 text-6xl mb-4"></i>
                    <p class="text-gray-500 text-lg">Belum ada riwayat penggajian</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($periods->hasPages())
            <div class="mt-6">
                {{ $periods->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
