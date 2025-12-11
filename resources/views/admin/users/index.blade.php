@extends('layouts.app')
@section('title', 'Manajemen User')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Users</h1>
            <p class="text-sm text-gray-600 mt-1">Manage system users</p>
        </div>
        
        @can('create users')
        <a href="{{ route('users.create') }}" class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition-colors">
            <i class="fas fa-plus mr-2"></i>Add User
        </a>
        @endcan
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-lg border border-gray-200">
        {{-- Search & Filter --}}
        <div class="p-4 border-b border-gray-200">
            <form method="GET" action="{{ route('users.index') }}" class="flex gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search users by name or email..." 
                           class="w-full px-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400">
                </div>
                <button type="submit" class="px-4 py-2 bg-gray-900 text-white text-sm rounded-lg hover:bg-gray-800 transition-colors">
                    <i class="fas fa-search"></i>
                </button>
                @if(request('search'))
                <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition-colors">
                    <i class="fas fa-times"></i>
                </a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">User</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Roles</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-900 font-medium">{{ $user->name }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-900">{{ $user->email }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
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
                                    <span class="px-2 py-1 {{ $roleColors[$role->name] ?? 'bg-gray-100 text-gray-700' }} text-xs rounded font-medium">
                                        <i class="fas {{ $roleIcons[$role->name] ?? 'fa-user' }} mr-1"></i>{{ ucwords(str_replace('_', ' ', $role->name)) }}
                                    </span>
                                @empty
                                    <span class="text-gray-400 text-xs">No role</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('users.show', $user) }}" class="p-1.5 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded transition-colors">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                @can('edit users')
                                <a href="{{ route('users.edit', $user) }}" class="p-1.5 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded transition-colors">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                                @endcan
                                @can('delete users')
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure?')" class="p-1.5 text-red-600 hover:text-red-700 hover:bg-red-50 rounded transition-colors">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mb-3">
                                    <i class="fas fa-users text-gray-400 text-xl"></i>
                                </div>
                                <p class="text-gray-600 font-medium">No users found</p>
                                <p class="text-gray-500 text-sm mt-1">Start by adding your first user</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
        <div class="p-4 border-t border-gray-200">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection