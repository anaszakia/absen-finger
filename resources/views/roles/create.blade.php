@extends('layouts.app')

@section('title', 'Create Role')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center space-x-4">
        <a href="{{ route('roles.index') }}" class="text-gray-500 hover:text-gray-700 transition-colors">
            <i class="fas fa-arrow-left text-lg"></i>
        </a>
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Create New Role</h1>
            <p class="text-gray-600 mt-1">Define a new role and assign permissions</p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('roles.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <!-- Role Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Role Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                       placeholder="Enter role name (e.g., manager, editor)"
                       required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-sm mt-1">Use lowercase with underscores for multi-word names (e.g., content_manager)</p>
            </div>

            <!-- Permissions -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-4">
                    Permissions <span class="text-gray-500">(optional)</span>
                </label>
                
                <!-- Select All Options -->
                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">Quick Actions:</span>
                        <div class="space-x-2">
                            <button type="button" onclick="selectAll()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Select All
                            </button>
                            <button type="button" onclick="selectNone()" class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                                Select None
                            </button>
                        </div>
                    </div>
                </div>

                @if($permissions->count() > 0)
                    <!-- Grouped Permissions -->
                    @php
                        $groupedPermissions = $permissions->groupBy(function($permission) {
                            $parts = explode(' ', $permission->name);
                            return count($parts) > 1 ? $parts[1] : 'general';
                        });
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($groupedPermissions as $group => $groupPermissions)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-3 capitalize">
                                    {{ ucwords(str_replace('_', ' ', $group)) }} Permissions
                                </h4>
                                <div class="space-y-2">
                                    @foreach($groupPermissions as $permission)
                                        <label class="flex items-center group cursor-pointer">
                                            <input type="checkbox" 
                                                   name="permissions[]" 
                                                   value="{{ $permission->id }}"
                                                   class="permission-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2"
                                                   {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-700 group-hover:text-gray-900">
                                                {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-key text-3xl mb-2"></i>
                        <p>No permissions available</p>
                    </div>
                @endif

                @error('permissions')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <a href="{{ route('roles.index') }}" class="px-4 py-2 text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors duration-200">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-lg font-medium transition-colors duration-200">
                    Create Role
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function selectAll() {
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = true);
}

function selectNone() {
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = false);
}
</script>
@endsection