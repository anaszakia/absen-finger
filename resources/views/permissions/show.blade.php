@extends('layouts.app')

@section('title', 'Detail Permission')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center space-x-4">
        <a href="{{ route('permissions.index') }}" class="text-gray-500 hover:text-gray-700 transition-colors">
            <i class="fas fa-arrow-left text-lg"></i>
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-semibold text-gray-900">Detail Permission</h1>
            <p class="text-gray-600 mt-1">Informasi lengkap permission</p>
        </div>
        <div class="flex gap-2">
            @can('edit permissions')
                <a href="{{ route('permissions.edit', $permission) }}" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Permission
                </a>
            @endcan
            @can('delete permissions')
                <button onclick="deletePermission({{ $permission->id }}, '{{ $permission->name }}')" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                    <i class="fas fa-trash mr-2"></i>
                    Delete
                </button>
            @endcan
        </div>
    </div>

    <!-- Permission Details Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-blue-50">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-key text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ $permission->name }}</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Permission ID</label>
                    <p class="text-gray-900 font-medium">{{ $permission->id }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Guard Name</label>
                    <p class="text-gray-900 font-medium">{{ $permission->guard_name }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Created At</label>
                    <p class="text-gray-900 font-medium">
                        {{ $permission->created_at->format('d M Y, H:i') }}
                        <span class="text-gray-500 text-sm">({{ $permission->created_at->diffForHumans() }})</span>
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Last Updated</label>
                    <p class="text-gray-900 font-medium">
                        {{ $permission->updated_at->format('d M Y, H:i') }}
                        <span class="text-gray-500 text-sm">({{ $permission->updated_at->diffForHumans() }})</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Roles that have this permission -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-user-shield mr-2 text-blue-600"></i>
                Role yang Memiliki Permission Ini
            </h3>
            <p class="text-sm text-gray-600 mt-1">Daftar role yang memiliki akses permission ini</p>
        </div>
        
        <div class="p-6">
            @if($permission->roles->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($permission->roles as $role)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3
                                        @if($role->name === 'super_admin') bg-red-100
                                        @elseif($role->name === 'admin') bg-blue-100
                                        @else bg-green-100 @endif">
                                        <i class="fas 
                                            @if($role->name === 'super_admin') fa-crown text-red-600
                                            @elseif($role->name === 'admin') fa-shield-alt text-blue-600
                                            @else fa-user text-green-600 @endif"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">
                                            {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                        </h4>
                                        <p class="text-xs text-gray-500">{{ $role->permissions->count() }} permissions</p>
                                    </div>
                                </div>
                                <a href="{{ route('roles.show', $role) }}" class="text-blue-600 hover:text-blue-700 text-sm">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-user-shield text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500">Permission ini belum di-assign ke role manapun</p>
                    <p class="text-sm text-gray-400 mt-1">Assign permission ini ke role melalui halaman role management</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Users with this permission (through roles) -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-users mr-2 text-green-600"></i>
                Users dengan Permission Ini
            </h3>
            <p class="text-sm text-gray-600 mt-1">Users yang memiliki permission ini melalui role mereka</p>
        </div>
        
        <div class="p-6">
            @php
                $usersWithPermission = \App\Models\User::permission($permission->name)->get();
            @endphp
            
            @if($usersWithPermission->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($usersWithPermission as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center mr-3">
                                                <span class="text-white text-sm font-medium">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </span>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $user->email }}</td>
                                    <td class="px-4 py-3">
                                        @foreach($user->roles as $userRole)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                                @if($userRole->name === 'super_admin') bg-red-100 text-red-800 
                                                @elseif($userRole->name === 'admin') bg-blue-100 text-blue-800
                                                @else bg-green-100 text-green-800 @endif mr-1">
                                                {{ ucwords(str_replace('_', ' ', $userRole->name)) }}
                                            </span>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-users text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500">Tidak ada user yang memiliki permission ini</p>
                </div>
            @endif
        </div>
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
        text: `Are you sure you want to delete the permission "${permissionName}"? This action cannot be undone and will remove this permission from all roles.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('delete-form');
            form.action = `{{ route('permissions.destroy', $permission) }}`;
            form.submit();
        }
    });
}
</script>
@endsection
