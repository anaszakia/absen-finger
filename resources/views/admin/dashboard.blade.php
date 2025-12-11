@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div x-data="dashboardData()" class="space-y-6">
    <!-- Simple Welcome Header -->
    <div class="bg-white rounded-xl p-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard Admin</h1>
                <p class="text-gray-600 text-sm mt-1">Selamat datang, {{ auth()->user()->name }}</p>
            </div>
            <div class="text-right text-sm text-gray-600">
                <div>{{ now()->format('d F Y') }}</div>
                <div x-text="currentTime" class="font-medium text-gray-900"></div>
            </div>
        </div>
    </div>

    <!-- Simple Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Users -->
        <div class="bg-white p-5 rounded-lg border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-white text-sm"></i>
                </div>
                <span class="text-xs text-gray-500">+{{ $todayRegistrations }} hari ini</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($totalUsers) }}</div>
            <div class="text-xs text-gray-600 mt-1">Total Users</div>
        </div>

        <!-- Total Admins -->
        <div class="bg-white p-5 rounded-lg border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-shield text-white text-sm"></i>
                </div>
                <span class="text-xs text-gray-500">{{ number_format($totalRegularUsers) }} users</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($totalAdmins) }}</div>
            <div class="text-xs text-gray-600 mt-1">Total Admin</div>
        </div>

        <!-- Today Logins -->
        <div class="bg-white p-5 rounded-lg border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-sign-in-alt text-white text-sm"></i>
                </div>
                <span class="text-xs text-gray-500">{{ $thisWeekRegistrations }} minggu ini</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($todayLogins) }}</div>
            <div class="text-xs text-gray-600 mt-1">Login Hari Ini</div>
        </div>

        <!-- Audit Logs -->
        <div class="bg-white p-5 rounded-lg border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-white text-sm"></i>
                </div>
                <span class="text-xs text-gray-500">{{ $thisMonthRegistrations }} bulan ini</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($totalAuditLogs) }}</div>
            <div class="text-xs text-gray-600 mt-1">Total Audit Log</div>
        </div>
    </div>

    <!-- Simple Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- User Growth Chart -->
        <div class="bg-white p-6 rounded-lg border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Pertumbuhan User</h3>
                    <p class="text-xs text-gray-500 mt-1">7 Bulan Terakhir</p>
                </div>
                <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fas fa-sync-alt text-gray-400 text-xs"></i>
                </button>
            </div>
            <div class="h-64">
                <canvas id="userGrowthChart"></canvas>
            </div>
        </div>

        <!-- Login Statistics -->
        <div class="bg-white p-6 rounded-lg border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Statistik Login</h3>
                    <p class="text-xs text-gray-500 mt-1">7 Hari Terakhir</p>
                </div>
                <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fas fa-sync-alt text-gray-400 text-xs"></i>
                </button>
            </div>
            <div class="h-64">
                <canvas id="loginStatsChart"></canvas>
            </div>
        </div>
    </div>
    <!-- Simple Data Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Users -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">User Terbaru</h3>
                    <a href="{{ route('users.index') }}" class="text-xs text-gray-900 hover:underline">
                        Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="p-4">
                <div class="space-y-2">
                    @forelse($recentUsers as $user)
                    <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-all">
                        <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-bold text-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            @php
                                $userRoles = $user->roles;
                                $roleName = $userRoles->first() ? ucwords(str_replace('_', ' ', $userRoles->first()->name)) : 'User';
                            @endphp
                            <span class="inline-block px-2 py-1 text-xs bg-gray-900 text-white rounded">{{ $roleName }}</span>
                            <p class="text-xs text-gray-500 mt-1">{{ $user->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-users text-gray-400 text-xl"></i>
                        </div>
                        <p class="text-gray-600 font-medium text-sm">Belum ada user terbaru</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Aktivitas Terbaru</h3>
                    <a href="{{ route('audit.index') }}" class="text-xs text-gray-900 hover:underline">
                        Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="p-4">
                <div class="space-y-2">
                    @forelse($recentActivity as $activity)
                    <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-all">
                        <div class="w-8 h-8 bg-gray-900 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-circle text-white text-xs"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900">{{ $activity->user ? $activity->user->name : 'Unknown' }}</p>
                                <span class="px-2 py-1 bg-gray-900 text-white text-xs rounded">{{ $activity->action }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-history text-gray-400 text-xl"></i>
                        </div>
                        <p class="text-gray-600 font-medium text-sm">Belum ada aktivitas</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* Simple transitions */
.transition-all {
    transition: all 0.2s ease;
}
</style>

<script>
function dashboardData() {
    return {
        currentTime: new Date().toLocaleTimeString('id-ID'),
        
        init() {
            // Update time every second
            setInterval(() => {
                this.currentTime = new Date().toLocaleTimeString('id-ID');
            }, 1000);
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // User Growth Chart - Simple Style
    const userGrowthCtx = document.getElementById('userGrowthChart');
    if (userGrowthCtx) {
        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: @json($userGrowthData['months']),
                datasets: [{
                    label: 'User Baru',
                    data: @json($userGrowthData['userCounts']),
                    borderColor: '#111827',
                    backgroundColor: 'rgba(17, 24, 39, 0.05)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#111827',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#111827',
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderWidth: 0,
                        cornerRadius: 6,
                        displayColors: false,
                        padding: 12
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6' },
                        ticks: { 
                            precision: 0,
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    }

    // Login Statistics Chart - Simple Style
    const loginStatsCtx = document.getElementById('loginStatsChart');
    if (loginStatsCtx) {
        new Chart(loginStatsCtx, {
            type: 'bar',
            data: {
                labels: @json($loginStats['days']),
                datasets: [{
                    label: 'Login',
                    data: @json($loginStats['loginCounts']),
                    backgroundColor: '#111827',
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#111827',
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderWidth: 0,
                        cornerRadius: 6,
                        displayColors: false,
                        padding: 12
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6' },
                        ticks: { 
                            precision: 0,
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endsection
