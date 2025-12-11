<!-- Mobile Header with Toggle -->
<div class="fixed top-0 left-0 right-0 z-40 lg:hidden bg-white border-b border-gray-200 h-14 flex items-center px-4">
    <button id="sidebar-toggle" class="p-2 text-gray-900 hover:bg-gray-100 rounded transition-colors">
        <i class="fas fa-bars text-lg"></i>
    </button>
    <div class="flex items-center space-x-2 ml-3">
        <div class="w-7 h-7 bg-gray-900 rounded-lg flex items-center justify-center">
            <i class="fas fa-layer-group text-white text-xs"></i>
        </div>
        <span class="text-base font-semibold text-gray-900">Template</span>
    </div>
</div>

<!-- Sidebar Overlay for Mobile -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>

<!-- Sidebar -->
<div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white transform -translate-x-full lg:translate-x-0 lg:static lg:z-auto flex flex-col border-r border-gray-200 transition-transform duration-200">
    <!-- Sidebar Header -->
    <div class="flex items-center justify-between h-14 px-4 border-b border-gray-200">
        <div class="flex items-center space-x-2">
            <div class="w-7 h-7 bg-gray-900 rounded-lg flex items-center justify-center">
                <i class="fas fa-layer-group text-white text-xs"></i>
            </div>
            <span class="text-base font-semibold text-gray-900 sidebar-text">Template</span>
        </div>
        <button id="sidebar-close" class="lg:hidden p-1.5 text-gray-500 hover:text-gray-900 rounded">
            <i class="fas fa-times text-sm"></i>
        </button>
    </div>
    
    <!-- Navigation -->
    <nav class="flex-1 px-3 py-4 overflow-y-auto">
        <ul class="space-y-1">
            <!-- Dashboard -->
            <li>
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center px-3 py-2 text-sm text-gray-700 rounded hover:bg-gray-100 transition-colors {{ request()->routeIs('dashboard') ? 'bg-gray-900 text-white hover:bg-gray-900' : '' }}">
                    <i class="fas fa-home w-4 text-sm mr-3"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            
            <!-- Management Section -->
            @if(auth()->user()->can('view users') || auth()->user()->can('view audit logs') || auth()->user()->can('view roles') || auth()->user()->can('view permissions'))
                <li>
                    <button onclick="toggleDropdown()" class="flex items-center justify-between w-full px-3 py-2 text-sm text-gray-700 rounded hover:bg-gray-100 transition-colors">
                        <div class="flex items-center">
                            <i class="fas fa-users-cog w-4 text-sm mr-3"></i>
                            <span class="sidebar-text">Management</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200 sidebar-arrow" id="dropdown-arrow"></i>
                    </button>
                    
                    <!-- Dropdown -->
                    <div id="dropdown-content" class="hidden mt-1 ml-7 space-y-1">
                        @can('view users')
                            <a href="{{ route('users.index') }}" class="flex items-center px-3 py-1.5 text-xs text-gray-600 rounded hover:bg-gray-100 transition-colors {{ request()->routeIs('users.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                                <span class="sidebar-text">Users</span>
                            </a>
                        @endcan
                        
                        @can('view audit logs')
                            <a href="{{ route('audit.index') }}" class="flex items-center px-3 py-1.5 text-xs text-gray-600 rounded hover:bg-gray-100 transition-colors {{ request()->routeIs('audit.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                                <span class="sidebar-text">Audit Log</span>
                            </a>
                        @endcan
                        
                        @can('view roles')
                            <a href="{{ route('roles.index') }}" class="flex items-center px-3 py-1.5 text-xs text-gray-600 rounded hover:bg-gray-100 transition-colors {{ request()->routeIs('roles.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                                <span class="sidebar-text">Roles</span>
                            </a>
                        @endcan
                        
                        @can('view permissions')
                            <a href="{{ route('permissions.index') }}" class="flex items-center px-3 py-1.5 text-xs text-gray-600 rounded hover:bg-gray-100 transition-colors {{ request()->routeIs('permissions.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                                <span class="sidebar-text">Permissions</span>
                            </a>
                        @endcan
                    </div>
                </li>
            @endif
            
            <!-- HR & Attendance Section -->
            @if(auth()->user()->can('view employees') || auth()->user()->can('view machines') || auth()->user()->can('view attendances'))
                <li>
                    <button onclick="toggleHrDropdown()" class="flex items-center justify-between w-full px-3 py-2 text-sm text-gray-700 rounded hover:bg-gray-100 transition-colors">
                        <div class="flex items-center">
                            <i class="fas fa-fingerprint w-4 text-sm mr-3"></i>
                            <span class="sidebar-text">HR & Absensi</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200 sidebar-arrow" id="hr-dropdown-arrow"></i>
                    </button>
                    
                    <!-- Dropdown -->
                    <div id="hr-dropdown-content" class="hidden mt-1 ml-7 space-y-1">
                        @can('view employees')
                            <a href="{{ route('employees.index') }}" class="flex items-center px-3 py-1.5 text-xs text-gray-600 rounded hover:bg-gray-100 transition-colors {{ request()->routeIs('employees.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                                <span class="sidebar-text">Karyawan</span>
                            </a>
                        @endcan
                        
                        @can('view machines')
                            <a href="{{ route('machines.index') }}" class="flex items-center px-3 py-1.5 text-xs text-gray-600 rounded hover:bg-gray-100 transition-colors {{ request()->routeIs('machines.index') ? 'bg-gray-100 text-gray-900' : '' }}">
                                <span class="sidebar-text">Mesin Absensi</span>
                            </a>
                            <a href="{{ route('machines.fingerspot-setup') }}" class="flex items-center px-3 py-1.5 text-xs text-gray-600 rounded hover:bg-gray-100 transition-colors {{ request()->routeIs('machines.fingerspot-setup') ? 'bg-gray-100 text-gray-900' : '' }}">
                                <i class="fas fa-fingerprint text-blue-600 mr-1"></i>
                                <span class="sidebar-text">Setup Fingerspot</span>
                            </a>
                        @endcan
                        
                        @can('view attendances')
                            <a href="{{ route('attendances.index') }}" class="flex items-center px-3 py-1.5 text-xs text-gray-600 rounded hover:bg-gray-100 transition-colors {{ request()->routeIs('attendances.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                                <span class="sidebar-text">Rekap Absensi</span>
                            </a>
                        @endcan
                    </div>
                </li>
            @endif
            
            <!-- Reports -->
            @hasrole('super_admin')
                <li>
                    <a href="#" class="flex items-center px-3 py-2 text-sm text-gray-700 rounded hover:bg-gray-100 transition-colors">
                        <i class="fas fa-chart-line w-4 text-sm mr-3"></i>
                        <span class="sidebar-text">Reports</span>
                    </a>
                </li>
            @endhasrole
            
            <!-- Profile -->
            @can('edit profile')
                <li>
                    <a href="{{ route('profile.edit') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 rounded hover:bg-gray-100 transition-colors {{ request()->routeIs('profile.*') ? 'bg-gray-900 text-white hover:bg-gray-900' : '' }}">
                        <i class="fas fa-user w-4 text-sm mr-3"></i>
                        <span class="sidebar-text">Profile</span>
                    </a>
                </li>
            @endcan
            
            <!-- Settings -->
            @hasrole('super_admin')
                <li>
                    <a href="#" class="flex items-center px-3 py-2 text-sm text-gray-700 rounded hover:bg-gray-100 transition-colors">
                        <i class="fas fa-cog w-4 text-sm mr-3"></i>
                        <span class="sidebar-text">Settings</span>
                    </a>
                </li>
            @endhasrole
            
            <!-- Help -->
            <li>
                <a href="{{ route('help.center') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 rounded hover:bg-gray-100 transition-colors">
                    <i class="fas fa-question-circle w-4 text-sm mr-3"></i>
                    <span class="sidebar-text">Help</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- User Profile Section -->
    <div class="border-t border-gray-200 p-3">
        <div class="flex items-center space-x-2 p-2 cursor-pointer hover:bg-gray-50 rounded transition-colors" onclick="toggleUserDropdown()">
            <div class="w-8 h-8 bg-gray-900 rounded-lg flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : '?' }}
            </div>
            <div class="flex-1 min-w-0 sidebar-text">
                <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
            </div>
            <i class="fas fa-chevron-up text-xs text-gray-400 transition-transform duration-200 sidebar-arrow" id="user-dropdown-arrow"></i>
        </div>
        
        <!-- User Dropdown Menu -->
        <div id="user-dropdown-content" class="hidden mt-2 py-1 bg-white border border-gray-200 rounded shadow-sm">
            <a href="{{ route('profile.edit') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                <i class="fas fa-user-edit w-4 text-sm mr-3"></i>
                <span class="sidebar-text">Edit Profile</span>
            </a>
            <a href="#" id="btn-logout" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                <i class="fas fa-sign-out-alt w-4 text-sm mr-3"></i>
                <span class="sidebar-text">Logout</span>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </div>
    </div>
</div>

<style>
/* Smooth transitions only */
</style>

<script>
// Toggle dropdown
function toggleDropdown() {
    const dropdown = document.getElementById('dropdown-content');
    const arrow = document.getElementById('dropdown-arrow');
    dropdown.classList.toggle('hidden');
    arrow.classList.toggle('rotate-180');
}

// Toggle HR dropdown
function toggleHrDropdown() {
    const dropdown = document.getElementById('hr-dropdown-content');
    const arrow = document.getElementById('hr-dropdown-arrow');
    dropdown.classList.toggle('hidden');
    arrow.classList.toggle('rotate-180');
}

// Toggle user dropdown
function toggleUserDropdown() {
    const dropdown = document.getElementById('user-dropdown-content');
    const arrow = document.getElementById('user-dropdown-arrow');
    dropdown.classList.toggle('hidden');
    arrow.classList.toggle('rotate-180');
}

// Sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarClose = document.getElementById('sidebar-close');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    
    // Open sidebar (mobile)
    sidebarToggle?.addEventListener('click', function() {
        sidebar.classList.remove('-translate-x-full');
        sidebarOverlay.classList.remove('hidden');
    });
    
    // Close sidebar
    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        sidebarOverlay.classList.add('hidden');
    }
    
    sidebarClose?.addEventListener('click', closeSidebar);
    sidebarOverlay?.addEventListener('click', closeSidebar);
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('dropdown-content');
        const userDropdown = document.getElementById('user-dropdown-content');
        
        if (!event.target.closest('button') && dropdown && !dropdown.classList.contains('hidden')) {
            dropdown.classList.add('hidden');
            document.getElementById('dropdown-arrow')?.classList.remove('rotate-180');
        }
        
        if (!event.target.closest('.border-t') && userDropdown && !userDropdown.classList.contains('hidden')) {
            userDropdown.classList.add('hidden');
            document.getElementById('user-dropdown-arrow')?.classList.remove('rotate-180');
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('btn-logout').addEventListener('click', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Konfirmasi Logout',
            text: 'Apakah Anda yakin ingin keluar dari sistem?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Keluar',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logout-form').submit();
            }
        });
    });
</script>