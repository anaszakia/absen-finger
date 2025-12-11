<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.3/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('modal', {
                open: false
            });
        });
        
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'catalyst-gray': {
                            50: '#f9fafb',
                            100: '#f3f4f6',
                            200: '#e5e7eb',
                            300: '#d1d5db',
                            400: '#9ca3af',
                            500: '#6b7280',
                            600: '#4b5563',
                            700: '#374151',
                            800: '#1f2937',
                            900: '#111827',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        
        /* Alpine.js transitions */
        [x-cloak] { 
            display: none !important; 
        }
        
        .modal-transition {
            transition-property: opacity, transform;
            transition-duration: 300ms;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .modal-enter-start, .modal-leave-end {
            opacity: 0;
            transform: scale(0.95);
        }
        
        .modal-enter-end, .modal-leave-start {
            opacity: 1;
            transform: scale(1);
        }
        
        .backdrop-transition {
            transition-property: opacity;
            transition-duration: 200ms;
        }
        
        .backdrop-enter-start, .backdrop-leave-end {
            opacity: 0;
        }
        
        .backdrop-enter-end, .backdrop-leave-start {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('partials.sidebar')
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            {{-- <!-- Header -->
            @include('partials.header') --}}
            
            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
            
            <!-- Footer -->
            @include('partials.footer')
        </div>
    </div>

   <script>
        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Toggle notifications dropdown
        function toggleNotifications() {
            const dropdown = document.getElementById('notifications-dropdown');
            const profileDropdown = document.getElementById('profile-dropdown');
            
            // Close profile dropdown if open
            if (!profileDropdown.classList.contains('hidden')) {
                profileDropdown.classList.add('hidden');
            }
            
            dropdown.classList.toggle('hidden');
        }

        // Toggle profile dropdown
        function toggleProfile() {
            const dropdown = document.getElementById('profile-dropdown');
            const notificationsDropdown = document.getElementById('notifications-dropdown');
            
            // Close notifications dropdown if open
            if (!notificationsDropdown.classList.contains('hidden')) {
                notificationsDropdown.classList.add('hidden');
            }
            
            dropdown.classList.toggle('hidden');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const notificationsDropdown = document.getElementById('notifications-dropdown');
            const profileDropdown = document.getElementById('profile-dropdown');
            
            // Check if click is outside notifications dropdown
            if (!event.target.closest('.relative') || !event.target.closest('button[onclick="toggleNotifications()"]')) {
                if (!event.target.closest('#notifications-dropdown')) {
                    notificationsDropdown?.classList.add('hidden');
                }
            }
            
            // Check if click is outside profile dropdown
            if (!event.target.closest('.relative') || !event.target.closest('button[onclick="toggleProfile()"]')) {
                if (!event.target.closest('#profile-dropdown')) {
                    profileDropdown?.classList.add('hidden');
                }
            }
        });

        // Close sidebar when clicking overlay
        document.getElementById('sidebar-overlay')?.addEventListener('click', toggleSidebar);

        // Close dropdowns when pressing Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.getElementById('notifications-dropdown')?.classList.add('hidden');
                document.getElementById('profile-dropdown')?.classList.add('hidden');
            }
        });
    </script>

    {{-- SweetAlert2 library --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Script untuk menampilkan notifikasi sukses --}}
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
        });
    </script>
    @endif

    {{-- Script untuk menampilkan notifikasi error --}}
    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '{{ session('error') }}',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
        });
    </script>
    @endif

    {{-- Script untuk menampilkan error validasi --}}
    @if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let errorMessages = '';
            @foreach($errors->all() as $error)
                errorMessages += '{{ $error }}\n';
            @endforeach

            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                text: errorMessages,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
        });
    </script>
    @endif

    {{-- Utility function untuk konfirmasi SweetAlert --}}
    <script>
        function confirmDelete(formElement, itemName = 'item') {
            event.preventDefault();
            Swal.fire({
                title: 'Hapus Item?',
                text: `Anda yakin ingin menghapus ${itemName}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then(res => {
                if (res.isConfirmed) formElement.submit();
            });
        }
    </script>
    
    @yield('scripts')
</body>
</html>