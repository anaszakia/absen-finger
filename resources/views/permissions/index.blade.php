@extends('layouts.app')

@section('title', 'Manajemen Permission')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Manajemen Permission</h1>
            <p class="text-gray-600 mt-1">Kelola permission sistem</p>
        </div>
        @can('create permissions')
            <a href="{{ route('permissions.create') }}" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                <i class="fas fa-plus mr-2"></i>
                Tambah Permission
            </a>
        @endcan
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                <span class="text-green-800">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                <span class="text-red-800">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Tabel Permission -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Daftar Permission</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nama Permission
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Role yang Memiliki
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Created At
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($permissions as $permission)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-key text-purple-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $permission->name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($permission->roles as $role)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                            @if($role->name === 'super_admin') bg-red-100 text-red-800 
                                            @else bg-blue-100 text-blue-800 @endif">
                                            @if($role->name === 'super_admin')
                                                <i class="fas fa-crown mr-1"></i>
                                            @endif
                                            {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                        </span>
                                    @empty
                                        <span class="text-gray-400 text-sm">Not assigned</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $permission->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <a href="{{ route('permissions.show', $permission) }}" class="text-gray-600 hover:text-gray-900 transition-colors">
                                    <i class="fas fa-eye mr-1"></i>
                                </a>
                                
                                @can('edit permissions')
                                    <a href="{{ route('permissions.edit', $permission) }}" class="text-gray-600 hover:text-gray-900 transition-colors">
                                        <i class="fas fa-edit mr-1"></i>
                                    </a>
                                @endcan
                                
                                @can('delete permissions')
                                    <button onclick="deletePermission({{ $permission->id }}, '{{ $permission->name }}')" class="text-gray-600 hover:text-gray-900 transition-colors">
                                        <i class="fas fa-trash mr-1"></i>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-key text-gray-300 text-4xl mb-2"></i>
                                    <p class="text-gray-500">No permissions found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($permissions->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $permissions->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function deletePermission(permissionId, permissionName) {
    Swal.fire({
        title: 'Delete Permission?',
        text: `Are you sure you want to delete the permission "${permissionName}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('delete-form');
            form.action = `{{ route('permissions.index') }}/${permissionId}`;
            form.submit();
        }
    });
}
</script>
@endsection