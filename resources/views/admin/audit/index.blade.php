@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
<div class="space-y-6">
    {{-- Success/Error Messages --}}
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    {{-- Header --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Audit Log</h1>
                <p class="text-sm text-gray-500 mt-1">Catatan aktivitas sensitif dalam sistem</p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <button onclick="toggleFilters()" class="bg-gray-100 hover:bg-gray-200 text-gray-900 px-4 py-2 rounded-lg transition-colors text-sm font-medium">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                
                <button onclick="exportData()" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg transition-colors text-sm font-medium">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </button>
                
                @if(request()->hasAny(['date_from', 'date_to', 'action', 'user_id', 'search']))
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-900">
                    <i class="fas fa-filter mr-1"></i>Filter Aktif
                </span>
                @endif
            </div>
        </div>
        
        {{-- Filter Form --}}
        <div id="filterForm" class="hidden mt-6 p-4 bg-gray-100 rounded-lg">
            <form method="GET" action="{{ route('audit.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Date Range --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                           class="w-full border-gray-300 rounded-lg text-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                           class="w-full border-gray-300 rounded-lg text-sm">
                </div>
                
                {{-- Action Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                    <select name="action" class="w-full border-gray-300 rounded-lg text-sm">
                        <option value="">Semua Action</option>
                        @foreach($actions as $action)
                        <option value="{{ $action }}" @selected(request('action') == $action)>
                            {{ ucfirst($action) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                {{-- User Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                    <select name="user_id" class="w-full border-gray-300 rounded-lg text-sm">
                        <option value="">Semua User</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>
                            {{ $user->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                {{-- Search --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Email, IP, atau Action..."
                           class="w-full border-gray-300 rounded-lg text-sm">
                </div>
                
                {{-- Buttons --}}
                <div class="lg:col-span-2 flex gap-2">
                    <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Apply Filter
                    </button>
                    <a href="{{ route('audit.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-900 px-4 py-2 rounded-lg text-sm font-medium">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Audit Log Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detail</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div>{{ $log->performed_at->format('d M Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $log->performed_at->format('H:i:s') }}</div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $log->user?->name ?? 'Unknown' }}
                            </div>
                            <div class="text-sm text-gray-500">{{ $log->user_email }}</div>
                            @if($log->user_role)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                {{ $log->user_role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ ucfirst($log->user_role) }}
                            </span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $log->action_badge }}">
                                {{ $log->action_text }}
                            </span>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div>{{ $log->route }}</div>
                            <div class="text-xs text-gray-500">{{ $log->method }}</div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->ip }}
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $log->status_code >= 200 && $log->status_code < 300 ? 'bg-green-100 text-green-800' : 
                                   ($log->status_code >= 400 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ $log->status_code }}
                            </span>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('audit.show', $log) }}" 
                               class="text-blue-600 hover:text-blue-900 font-medium">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <i class="fas fa-clipboard-list text-4xl mb-4"></i>
                                <p class="text-lg font-medium">Tidak ada audit log</p>
                                <p class="text-sm">Belum ada aktivitas yang tercatat</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        {{ $logs->links() }}
    </div>
    @endif
</div>

<script>
function toggleFilters() {
    const filterForm = document.getElementById('filterForm');
    filterForm.classList.toggle('hidden');
}

function exportData() {
    // Show loading state
    const exportBtn = event.target;
    const originalText = exportBtn.innerHTML;
    exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Exporting...';
    exportBtn.disabled = true;
    
    // Ambil parameter filter dari form
    const form = document.querySelector('#filterForm form');
    const formData = new FormData(form);
    
    // Buat URL dengan parameter
    const params = new URLSearchParams();
    for (let [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }
    
    // Tambahkan parameter dari URL saat ini juga
    const currentParams = new URLSearchParams(window.location.search);
    for (let [key, value] of currentParams.entries()) {
        if (value && !params.has(key)) {
            params.append(key, value);
        }
    }
    
    // Buat form untuk POST request
    const exportForm = document.createElement('form');
    exportForm.method = 'POST';
    exportForm.action = '{{ route('audit.export') }}';
    exportForm.style.display = 'none';
    
    // Tambahkan CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    exportForm.appendChild(csrfInput);
    
    // Tambahkan semua parameter sebagai hidden input
    for (let [key, value] of params.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        exportForm.appendChild(input);
    }
    
    // Submit form
    document.body.appendChild(exportForm);
    exportForm.submit();
    document.body.removeChild(exportForm);
    
    // Reset button state after a short delay
    setTimeout(() => {
        exportBtn.innerHTML = originalText;
        exportBtn.disabled = false;
    }, 3000);
}
</script>
@endsection
