@extends('layouts.app')
@section('title', 'User Details')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
            <p class="text-sm text-gray-600 mt-1">User Details</p>
        </div>
        
        <div class="flex gap-2">
            @can('edit users')
            <a href="{{ route('users.edit', $user) }}" class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition-colors">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            @endcan
            <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">User Information</h2>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="text-xs font-semibold text-gray-600 uppercase">Name</label>
                <p class="text-sm text-gray-900 mt-1">{{ $user->name }}</p>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 uppercase">Email</label>
                <p class="text-sm text-gray-900 mt-1">{{ $user->email }}</p>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 uppercase">User ID</label>
                <p class="text-sm text-gray-900 mt-1">#{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</p>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 uppercase">Status</label>
                <div class="mt-1">
                    <span class="px-2 py-1 bg-gray-900 text-white text-xs rounded">Active</span>
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 uppercase">Created At</label>
                <p class="text-sm text-gray-900 mt-1">{{ $user->created_at?->format('d M Y, H:i') ?? '-' }}</p>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 uppercase">Updated At</label>
                <p class="text-sm text-gray-900 mt-1">{{ $user->updated_at?->format('d M Y, H:i') ?? '-' }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Roles & Permissions</h2>
        
        <div class="space-y-4">
            <div>
                <label class="text-xs font-semibold text-gray-600 uppercase mb-2 block">Assigned Roles</label>
                <div class="flex flex-wrap gap-2">
                    @forelse($user->roles as $role)
                        @php
                            $roleColors = [
                                'super_admin' => 'bg-yellow-100 text-yellow-700',
                                'admin' => 'bg-purple-100 text-purple-700',
                                'manager' => 'bg-blue-100 text-blue-700',
                            ];
                            $roleIcons = [
                                'super_admin' => 'fa-crown',
                                'admin' => 'fa-user-shield',
                                'manager' => 'fa-user-tie',
                            ];
                        @endphp
                        <span class="px-3 py-1.5 {{ $roleColors[$role->name] ?? 'bg-gray-100 text-gray-700' }} text-sm rounded font-medium">
                            <i class="fas {{ $roleIcons[$role->name] ?? 'fa-user' }} mr-1"></i>{{ ucwords(str_replace('_', ' ', $role->name)) }}
                        </span>
                    @empty
                        <span class="text-gray-400 text-sm">No roles assigned</span>
                    @endforelse
                </div>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 uppercase mb-2 block">Permissions ({{ $user->getAllPermissions()->count() }})</label>
                
                @if($user->getAllPermissions()->count() > 0)
                    @php
                        $permissions = $user->getAllPermissions()->groupBy(function($permission) {
                            return explode(' ', $permission->name)[1] ?? 'other';
                        });
                    @endphp
                    <div class="space-y-3">
                        @foreach($permissions as $category => $perms)
                            <div>
                                <h5 class="text-xs font-medium text-gray-600 uppercase mb-2">
                                    <i class="fas fa-folder mr-1"></i>{{ ucwords($category) }}
                                </h5>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($perms as $permission)
                                        <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded">
                                            <i class="fas fa-check text-green-600 mr-1"></i>{{ ucwords(str_replace('_', ' ', $permission->name)) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-gray-400 text-sm">No permissions assigned</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
