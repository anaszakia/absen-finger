@extends('layouts.app')

@section('title', 'Detail Role')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('roles.index') }}" class="text-gray-500 hover:text-gray-700 transition-colors">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ ucwords(str_replace('_', ' ', $role->name)) }}
                    @if($role->name === 'super_admin')
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-2">
                            <i class="fas fa-crown mr-1"></i>
                            Role Sistem
                        </span>
                    @endif
                </h1>
                <p class="text-gray-600 mt-1">Detail role dan permission yang dimiliki</p>
            </div>
        </div>
        
        <div class="flex space-x-2">
            @can('edit roles')
                <a href="{{ route('roles.edit', $role) }}" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Role
                </a>
            @endcan
            
            @can('delete roles')
                @if($role->name !== 'super_admin')
                    <button onclick="deleteRole({{ $role->id }}, '{{ $role->name }}')" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                        <i class="fas fa-trash mr-2"></i>
                        Delete
                    </button>
                @endif
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Role Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Role</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nama Role</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ucwords(str_replace('_', ' ', $role->name)) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Permission</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $role->permissions->count() }} permission
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">User yang Memiliki</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $role->users->count() }} user
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Dibuat Pada</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $role->created_at->format('d M Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Permissions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Permission yang Dimiliki</h3>
                
                @if($role->permissions->count() > 0)
                    @php
                        $groupedPermissions = $role->permissions->groupBy(function($permission) {
                            $parts = explode(' ', $permission->name);
                            return count($parts) > 1 ? $parts[1] : 'general';
                        });
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($groupedPermissions as $group => $groupPermissions)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-3 capitalize">
                                    {{ ucwords(str_replace('_', ' ', $group)) }} Permission
                                </h4>
                                <div class="space-y-2">
                                    @foreach($groupPermissions as $permission)
                                        <div class="flex items-center">
                                            <i class="fas fa-check text-green-500 text-sm mr-2"></i>
                                            <span class="text-sm text-gray-700">
                                                {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-key text-3xl mb-2"></i>
                        <p>Tidak ada permission untuk role ini</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Users with this Role -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Users with this Role</h3>
                
                @if($role->users->count() > 0)
                    <div class="space-y-3">
                        @foreach($role->users as $user)
                            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-blue-600">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $user->name }}
                                    </p>
                                    <p class="text-xs text-gray-500 truncate">
                                        {{ $user->email }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-gray-500">
                        <i class="fas fa-users text-2xl mb-2"></i>
                        <p class="text-sm">No users assigned to this role</p>
                    </div>
                @endif
            </div>
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
function deleteRole(roleId, roleName) {
    Swal.fire({
        title: 'Delete Role?',
        text: `Are you sure you want to delete the role "${roleName}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('delete-form');
            form.action = `{{ route('roles.index') }}/${roleId}`;
            form.submit();
        }
    });
}
</script>
@endsection