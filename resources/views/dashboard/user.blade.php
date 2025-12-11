@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div x-data="userDashboard()" class="space-y-6">
    <!-- Simple Welcome Header -->
    <div class="bg-white rounded-xl p-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <!-- Simple Avatar -->
                <div class="w-16 h-16 bg-gray-900 rounded-lg flex items-center justify-center">
                    <span class="text-2xl font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                </div>
                
                <!-- User Info -->
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ auth()->user()->name }}</h1>
                    <p class="text-gray-600 text-sm mt-1">{{ auth()->user()->email }}</p>
                </div>
            </div>
            
            <!-- Simple Stats -->
            <div class="flex items-center space-x-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ auth()->user()->created_at->diffInDays(now()) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Hari Bergabung</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900" x-text="loginCount">0</div>
                    <div class="text-xs text-gray-500 mt-1">Total Login</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Profile Card -->
        <a href="{{ route('profile.edit') }}" class="group bg-white rounded-lg p-5 border border-gray-200 hover:border-gray-900 transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-edit text-white text-sm"></i>
                </div>
                <i class="fas fa-arrow-right text-gray-400 text-xs"></i>
            </div>
            <h3 class="text-sm font-semibold text-gray-900">Edit Profile</h3>
            <p class="text-xs text-gray-500 mt-1">Perbarui informasi profil</p>
        </a>

        <!-- Security Card -->
        <a href="{{ route('profile.security') }}" class="group bg-white rounded-lg p-5 border border-gray-200 hover:border-gray-900 transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shield-alt text-white text-sm"></i>
                </div>
                <i class="fas fa-arrow-right text-gray-400 text-xs"></i>
            </div>
            <h3 class="text-sm font-semibold text-gray-900">Keamanan</h3>
            <p class="text-xs text-gray-500 mt-1">Kelola password</p>
        </a>

        <!-- Settings Card -->
        <a href="{{ route('profile.settings') }}" class="group bg-white rounded-lg p-5 border border-gray-200 hover:border-gray-900 transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-cog text-white text-sm"></i>
                </div>
                <i class="fas fa-arrow-right text-gray-400 text-xs"></i>
            </div>
            <h3 class="text-sm font-semibold text-gray-900">Pengaturan</h3>
            <p class="text-xs text-gray-500 mt-1">Atur preferensi akun</p>
        </a>

        <!-- Help Card -->
        <a href="{{ route('help.center') }}" class="group bg-white rounded-lg p-5 border border-gray-200 hover:border-gray-900 transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-question-circle text-white text-sm"></i>
                </div>
                <i class="fas fa-arrow-right text-gray-400 text-xs"></i>
            </div>
            <h3 class="text-sm font-semibold text-gray-900">Bantuan</h3>
            <p class="text-xs text-gray-500 mt-1">Pusat bantuan</p>
        </a>
    </div>

    <!-- Activity Overview & Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Activity -->
        <div class="lg:col-span-2 bg-white rounded-lg p-6 border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Aktivitas Terakhir</h2>
                <button @click="refreshActivity" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fas fa-sync-alt text-gray-400 text-sm"></i>
                </button>
            </div>
            
            <div class="space-y-3">
                @forelse($recentActivities ?? [] as $activity)
                <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-all">
                    <div class="w-8 h-8 bg-gray-900 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-circle text-white text-xs"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-900">{{ ucfirst($activity->action ?? 'Aktivitas') }}</p>
                            <span class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-gray-600 mt-1">{{ $activity->description ?? 'Aktivitas sistem' }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-clock text-gray-400 text-xl"></i>
                    </div>
                    <p class="text-gray-600 font-medium">Belum ada aktivitas</p>
                    <p class="text-gray-500 text-xs mt-1">Aktivitas Anda akan muncul di sini</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Account Info -->
        <div class="space-y-4">
            <div class="bg-white rounded-lg p-5 border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Info Akun</h3>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-xs text-gray-600">Status</span>
                        <span class="px-2 py-1 bg-gray-900 text-white text-xs rounded">Aktif</span>
                    </div>
                    
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-xs text-gray-600">Email</span>
                        <i class="fas fa-check-circle text-gray-900"></i>
                    </div>
                    
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-xs text-gray-600">Bergabung</span>
                        <span class="text-xs text-gray-900 font-medium">{{ auth()->user()->created_at->format('d M Y') }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between py-2">
                        <span class="text-xs text-gray-600">Last Login</span>
                        <span class="text-xs text-gray-900 font-medium" x-text="lastLogin"></span>
                    </div>
                </div>
            </div>

            <div class="bg-gray-900 rounded-lg p-5 text-white">
                <div class="flex items-center mb-3">
                    <i class="fas fa-lightbulb mr-2 text-sm"></i>
                    <h3 class="text-sm font-semibold">Tips</h3>
                </div>
                <p class="text-xs leading-relaxed opacity-90">
                    Selalu logout setelah selesai menggunakan sistem untuk menjaga keamanan akun Anda.
                </p>
            </div>
        </div>
    </div>

    <!-- Simple Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Storage -->
        <div class="bg-white rounded-lg p-5 border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">Storage</h3>
                <i class="fas fa-database text-gray-400 text-sm"></i>
            </div>
            
            <div class="mb-3">
                <div class="flex justify-between text-xs text-gray-600 mb-2">
                    <span>2.5 GB</span>
                    <span>/ 10 GB</span>
                </div>
                <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div style="width:25%" class="h-full bg-gray-900"></div>
                </div>
            </div>
            
            <p class="text-xs text-gray-500">25% terpakai</p>
        </div>

        <!-- Activity -->
        <div class="bg-white rounded-lg p-5 border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">Activity</h3>
                <i class="fas fa-chart-line text-gray-400 text-sm"></i>
            </div>
            
            <div class="text-center py-4">
                <div class="text-3xl font-bold text-gray-900 mb-1">80%</div>
                <p class="text-xs text-gray-500">Score Aktivitas</p>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="bg-white rounded-lg p-5 border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">Quick Links</h3>
                <i class="fas fa-link text-gray-400 text-sm"></i>
            </div>
            
            <div class="space-y-2">
                <a href="#" class="flex items-center justify-between p-2 bg-gray-50 hover:bg-gray-100 rounded transition-colors">
                    <span class="text-xs font-medium text-gray-700">Dokumentasi</span>
                    <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                </a>
                
                <a href="#" class="flex items-center justify-between p-2 bg-gray-50 hover:bg-gray-100 rounded transition-colors">
                    <span class="text-xs font-medium text-gray-700">Support</span>
                    <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                </a>
                
                <a href="#" class="flex items-center justify-between p-2 bg-gray-50 hover:bg-gray-100 rounded transition-colors">
                    <span class="text-xs font-medium text-gray-700">Feedback</span>
                    <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function userDashboard() {
    return {
        currentDate: new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }),
        lastLogin: 'Hari ini',
        loginCount: {{ $totalLogins ?? 0 }},
        
        init() {
            // Animate login count
            this.animateCount();
        },
        
        animateCount() {
            let current = 0;
            const target = this.loginCount;
            const increment = target / 30;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    this.loginCount = target;
                    clearInterval(timer);
                } else {
                    this.loginCount = Math.floor(current);
                }
            }, 30);
        },
        
        refreshActivity() {
            console.log('Refreshing activity...');
            // Add your refresh logic here
        }
    }
}
</script>

<style>
/* Simple transitions */
.transition-all {
    transition: all 0.2s ease;
}
</style>
@endsection
