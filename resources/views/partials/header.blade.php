<!-- Header -->
<header class="bg-white shadow-sm border-b border-gray-200">
    <div class="flex items-center justify-between px-6 py-4">
        <!-- Mobile menu button -->
        <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-gray-500">
            <i class="fas fa-bars text-lg"></i>
        </button>
        
        <!-- Page Title -->
        <div class="flex-1 lg:flex-none">
            {{-- <h1 class="text-2xl font-semibold text-gray-900">
                @yield('page-title', 'Good afternoon, Erica')
            </h1> --}}
        </div>
        
        <!-- Right side content -->
        <div class="flex items-center space-x-4">
            <!-- Search -->
            <div class="hidden md:block">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500" 
                           placeholder="Search...">
                </div>
            </div>
            
            <!-- Notifications -->
            <div class="relative">
                <button onclick="toggleNotifications()" class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 rounded-full">
                    <i class="fas fa-bell text-lg"></i>
                    <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-400 ring-2 ring-white"></span>
                </button>
                
                <!-- Notifications Dropdown -->
                <div id="notifications-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        <div class="p-4 hover:bg-gray-50 border-b border-gray-100">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-ticket-alt text-blue-600 text-xs"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">New ticket sale</p>
                                    <p class="text-xs text-gray-500">Bear Hug: Live in Concert - $80.00</p>
                                    <p class="text-xs text-gray-400 mt-1">2 minutes ago</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 hover:bg-gray-50 border-b border-gray-100">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-chart-line text-green-600 text-xs"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Revenue milestone reached</p>
                                    <p class="text-xs text-gray-500">Monthly revenue exceeded $2.5M</p>
                                    <p class="text-xs text-gray-400 mt-1">1 hour ago</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 hover:bg-gray-50">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-calendar text-purple-600 text-xs"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Event reminder</p>
                                    <p class="text-xs text-gray-500">Six Fingers DJ Set starts in 2 hours</p>
                                    <p class="text-xs text-gray-400 mt-1">3 hours ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border-t border-gray-200">
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View all notifications</a>
                    </div>
                </div>
            </div>
            
            <!-- Profile dropdown -->
            <div class="relative">
                <button onclick="toggleProfile()" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <img class="h-8 w-8 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="User avatar">
                </button>
                
                <!-- Profile Dropdown -->
                <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex items-center space-x-3">
                            <img class="w-10 h-10 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="User avatar">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Erica</p>
                                <p class="text-xs text-gray-500">erica@example.com</p>
                            </div>
                        </div>
                    </div>
                    <div class="py-2">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-user w-4 h-4 mr-3 text-gray-400"></i>
                            Your Profile
                        </a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-cog w-4 h-4 mr-3 text-gray-400"></i>
                            Settings
                        </a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-question-circle w-4 h-4 mr-3 text-gray-400"></i>
                            Help & Support
                        </a>
                    </div>
                    <div class="border-t border-gray-200 py-2">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-sign-out-alt w-4 h-4 mr-3 text-gray-400"></i>
                            Sign out
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>