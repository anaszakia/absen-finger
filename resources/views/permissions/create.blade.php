@extends('layouts.app')

@section('title', 'Buat Permission')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center space-x-4">
        <a href="{{ route('permissions.index') }}" class="text-gray-500 hover:text-gray-700 transition-colors">
            <i class="fas fa-arrow-left text-lg"></i>
        </a>
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Buat Permission Baru</h1>
            <p class="text-gray-600 mt-1">Buat permission baru untuk sistem</p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('permissions.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <!-- Permission Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Permission <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}"
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

            <!-- Permission Examples -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-2">
                    <i class="fas fa-lightbulb mr-2"></i>
                    Permission Examples
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-blue-800">
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
                <button type="submit" class="px-6 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-lg font-medium transition-colors duration-200">
                    Create Permission
                </button>
            </div>
        </form>
    </div>
</div>
@endsection