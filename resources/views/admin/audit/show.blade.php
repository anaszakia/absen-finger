@extends('layouts.app')

@section('title', 'Detail Audit Log')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Audit Log</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Log ID: #{{ str_pad($auditLog->id, 6, '0', STR_PAD_LEFT) }}
                </p>
            </div>
            
            <a href="{{ route('audit.index') }}" 
               class="bg-gray-100 hover:bg-gray-200 text-gray-900 px-4 py-2 rounded-lg transition-colors text-sm font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Basic Information --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Dasar</h2>
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Waktu</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $auditLog->performed_at->format('d M Y, H:i:s') }}
                        </dd>
                        <dd class="text-xs text-gray-500">
                            {{ $auditLog->performed_at->diffForHumans() }}
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Action</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $auditLog->action_badge }}">
                                {{ $auditLog->action_text }}
                            </span>
                        </dd>
                    </div>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Route & Method</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $auditLog->route }}</dd>
                    <dd class="text-xs text-gray-500">{{ $auditLog->method }} - {{ $auditLog->controller }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">URL</dt>
                    <dd class="mt-1 text-sm text-gray-900 break-all">{{ $auditLog->url }}</dd>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status Code</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $auditLog->status_code >= 200 && $auditLog->status_code < 300 ? 'bg-green-100 text-green-800' : 
                                   ($auditLog->status_code >= 400 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ $auditLog->status_code }}
                            </span>
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $auditLog->ip }}</dd>
                    </div>
                </div>
            </div>
        </div>

        {{-- User Information --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi User</h2>
            
            <div class="space-y-4">
                @if($auditLog->user)
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr($auditLog->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-900">{{ $auditLog->user->name }}</div>
                        <div class="text-sm text-gray-500">{{ $auditLog->user_email }}</div>
                    </div>
                </div>
                @else
                <div class="text-sm text-gray-500">
                    <i class="fas fa-user-slash mr-2"></i>User tidak ditemukan atau telah dihapus
                </div>
                @endif
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">User ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $auditLog->user_id ?? 'N/A' }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $auditLog->user_email ?? 'N/A' }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Role</dt>
                    <dd class="mt-1">
                        @if($auditLog->user_role)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                            {{ $auditLog->user_role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ ucfirst($auditLog->user_role) }}
                        </span>
                        @else
                        <span class="text-sm text-gray-500">N/A</span>
                        @endif
                    </dd>
                </div>
            </div>
        </div>
    </div>

    {{-- User Agent & Request Data --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- User Agent --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">User Agent</h2>
            <div class="text-sm text-gray-700 break-all p-3 bg-gray-50 rounded-lg">
                {{ $auditLog->user_agent ?? 'N/A' }}
            </div>
        </div>

        {{-- Request Data --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Request Data</h2>
            @if($auditLog->request_data && count($auditLog->request_data) > 0)
            <div class="space-y-2">
                @foreach($auditLog->request_data as $key => $value)
                <div class="flex">
                    <dt class="text-sm font-medium text-gray-500 w-1/3">{{ $key }}:</dt>
                    <dd class="text-sm text-gray-900 w-2/3 break-all">
                        @if(is_array($value))
                            {{ json_encode($value) }}
                        @else
                            {{ $value }}
                        @endif
                    </dd>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-sm text-gray-500 p-3 bg-gray-50 rounded-lg">
                Tidak ada request data
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
