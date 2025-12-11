@extends('layouts.app')

@section('title', 'Edit Permission')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center space-x-4">
        <a href="{{ route('permissions.index') }}" class="text-gray-500 hover:text-gray-700 transition-colors">
            <i class="fas fa-arrow-left text-lg"></i>
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-semibold text-gray-900">Edit Permission</h1>
            <p class="text-gray-600 mt-1">Update informasi permission</p>
        </div>
        <a href="{{ route('permissions.show', $permission) }}" class="text-gray-600 hover:text-gray-700 transition-colors flex items-center text-sm">
            <i class="fas fa-eye mr-1"></i> View Details
        </a>
    </div>

    <!-- Current Permission Info -->
    <div class="bg-gradient-to-r from-purple-50 to-blue-50 border border-purple-200 rounded-lg p-4">
        <div class="flex items-center">
            <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-key text-white"></i>
            </div>
            <div>
                <p class="text-sm text-gray-600">Currently editing:</p>
                <p class="font-semibold text-gray-900">{{ ucwords(str_replace('_', ' ', $permission->name)) }}</p>
                <p class="text-xs text-gray-500 font-mono">{{ $permission->name }}</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('permissions.update', $permission) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Permission Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Permission <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name', $permission->name) }}"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @else border-gray-300 @enderror"
                       placeholder="Masukkan nama permission (misal: kelola posting, lihat laporan)"
                       required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <div class="mt-2 text-sm text-gray-500">
                    <p class="mb-1"><strong>Konvensi Penamaan:</strong></p>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        <li>Use lowercase with spaces (e.g., "view users", "create posts")</li>
                        <li>Start with action verb (view, create, edit, delete, manage)</li>
                        <li>Be specific and descriptive</li>
                        <li>Avoid spaces at the beginning or end</li>
                    </ul>
                </div>
            </div>

            <!-- Warning Box -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-3"></i>
                    <div>
                        <h4 class="font-medium text-yellow-900 mb-1">Peringatan</h4>
                        <p class="text-sm text-yellow-800">
                            Mengubah nama permission akan mempengaruhi semua role yang menggunakan permission ini. 
                            Pastikan untuk memperbarui kode yang menggunakan permission name ini jika diperlukan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Impact Information -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-3">
                    <i class="fas fa-info-circle mr-2"></i>
                    Impact of Change
                </h4>
                <div class="space-y-2 text-sm text-blue-800">
                    <div class="flex items-start">
                        <i class="fas fa-user-shield mt-1 mr-2"></i>
                        <div>
                            <strong>Roles affected:</strong> 
                            @if($permission->roles->count() > 0)
                                <span class="font-semibold">{{ $permission->roles->count() }}</span> role(s)
                                <span class="text-xs">({{ $permission->roles->pluck('name')->implode(', ') }})</span>
                            @else
                                <span class="text-gray-500">No roles assigned</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-users mt-1 mr-2"></i>
                        <div>
                            <strong>Users affected:</strong> 
                            @php
                                $usersCount = \App\Models\User::permission($permission->name)->count();
                            @endphp
                            @if($usersCount > 0)
                                <span class="font-semibold">{{ $usersCount }}</span> user(s)
                            @else
                                <span class="text-gray-500">No users have this permission</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-clock mt-1 mr-2"></i>
                        <div>
                            <strong>Last updated:</strong> {{ $permission->updated_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permission Examples -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">
                    <i class="fas fa-lightbulb mr-2"></i>
                    Permission Examples
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-700">
                    <div class="space-y-1">
                        <p><strong>User Management:</strong></p>
                        <ul class="list-disc list-inside ml-2 space-y-0.5 text-xs">
                            <li>view users</li>
                            <li>create users</li>
                            <li>edit users</li>
                            <li>delete users</li>
                        </ul>
                    </div>
                    <div class="space-y-1">
                        <p><strong>Content Management:</strong></p>
                        <ul class="list-disc list-inside ml-2 space-y-0.5 text-xs">
                            <li>view posts</li>
                            <li>create posts</li>
                            <li>publish posts</li>
                            <li>moderate comments</li>
                        </ul>
                    </div>
                    <div class="space-y-1">
                        <p><strong>System Access:</strong></p>
                        <ul class="list-disc list-inside ml-2 space-y-0.5 text-xs">
                            <li>access admin panel</li>
                            <li>view reports</li>
                            <li>manage settings</li>
                            <li>view audit logs</li>
                        </ul>
                    </div>
                    <div class="space-y-1">
                        <p><strong>Financial:</strong></p>
                        <ul class="list-disc list-inside ml-2 space-y-0.5 text-xs">
                            <li>view transactions</li>
                            <li>process payments</li>
                            <li>generate invoices</li>
                            <li>manage billing</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <a href="{{ route('permissions.index') }}" class="px-4 py-2 text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors duration-200">
                    Cancel
                </a>
                <div class="flex gap-2">
                    <a href="{{ route('permissions.show', $permission) }}" class="px-4 py-2 text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors duration-200">
                        View Details
                    </a>
                    <button type="submit" class="px-6 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-lg font-medium transition-colors duration-200">
                        Update Permission
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
