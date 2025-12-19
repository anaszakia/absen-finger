<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facca Apparel - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50 flex">
    <!-- Left Side - Assets -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-slate-800 to-slate-900 p-12 flex-col justify-between relative overflow-hidden">
        <!-- Decorative circles -->
        <div class="absolute top-20 left-20 w-72 h-72 bg-slate-700 rounded-full opacity-20 blur-3xl"></div>
        <div class="absolute bottom-20 right-20 w-96 h-96 bg-slate-600 rounded-full opacity-20 blur-3xl"></div>
        
        <!-- Logo & Brand -->
        <div class="relative z-10 fade-in">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shadow-lg">
                    <div class="w-6 h-6 bg-slate-800 rounded"></div>
                </div>
                <h1 class="text-3xl font-bold text-white">Facca Apparel</h1>
            </div>
            <p class="text-slate-300 text-lg max-w-md leading-relaxed">
                Sistem untuk mengelola Absensi dan Penggajian Perusahaan anda dengan mudah dan efisien
            </p>
        </div>

        <!-- Illustration -->
        <div class="relative z-10 flex items-center justify-center my-8">
            <div class="float-animation">
                <svg class="w-96 h-96" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Document/Screen illustration -->
                    <rect x="80" y="60" width="240" height="280" rx="12" fill="#475569" opacity="0.4"/>
                    <rect x="60" y="80" width="240" height="280" rx="12" fill="#64748b" opacity="0.6"/>
                    <rect x="40" y="100" width="240" height="280" rx="12" fill="#94a3b8"/>
                    
                    <!-- Lock icon -->
                    <circle cx="160" cy="240" r="40" fill="white" opacity="0.9"/>
                    <rect x="148" y="245" width="24" height="30" rx="4" fill="#1e293b"/>
                    <path d="M148 245V235C148 228.373 153.373 223 160 223C166.627 223 172 228.373 172 235V245" 
                          stroke="#1e293b" stroke-width="4" stroke-linecap="round" fill="none"/>
                </svg>
            </div>
        </div>

        <!-- Footer text -->
        <div class="relative z-10 text-slate-400 text-sm fade-in">
            <p>&copy; 2025 Facca Apparel. All rights reserved.</p>
        </div>
    </div>

    <!-- Right Side - Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
        <div class="w-full max-w-md fade-in">
            <!-- Mobile Logo -->
            <div class="lg:hidden flex items-center justify-center space-x-3 mb-8">
                <div class="w-10 h-10 bg-slate-800 rounded-lg flex items-center justify-center">
                    <div class="w-5 h-5 bg-white rounded"></div>
                </div>
                <h1 class="text-2xl font-bold text-slate-800">Facca Apparel</h1>
            </div>

            <!-- Title -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-slate-800 mb-2">Selamat Datang</h2>
                <p class="text-slate-500">Silakan masuk ke akun Anda</p>
            </div>

            <!-- Session Status (Success Message) -->
            @if (session('status'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mr-3 mt-0.5"></i>
                        <div>
                            <p class="text-sm text-green-800 font-medium">Berhasil!</p>
                            <p class="text-sm text-green-700 mt-1">{{ session('status') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                
                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email"
                        class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-400 focus:border-transparent transition-all @error('email') border-red-400 @enderror"
                        placeholder="nama@email.com"
                        value="{{ old('email') }}"
                        required autofocus
                    >
                    @error('email')
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password"
                            class="w-full px-4 py-3 pr-12 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-400 focus:border-transparent transition-all @error('password') border-red-400 @enderror"
                            placeholder="Masukkan password"
                            required
                        >
                        <button 
                            type="button" 
                            id="togglePassword" 
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                        >
                            <i class="fas fa-eye text-sm" id="eyeIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            id="remember" 
                            name="remember" 
                            type="checkbox" 
                            class="h-4 w-4 text-slate-800 focus:ring-slate-400 border-slate-300 rounded"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        <label for="remember" class="ml-2 block text-sm text-slate-700">Ingat Saya</label>
                    </div>
                    <a href="/forgot-password" class="text-sm text-slate-600 hover:text-slate-800 transition-colors">
                        Lupa password?
                    </a>
                </div>

                <!-- Login Button -->
                <button 
                    type="submit" 
                    class="w-full py-3 px-4 bg-slate-800 hover:bg-slate-900 text-white font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
                >
                    Masuk
                </button>
            </form>
        </div>
    </div>

    <script>
        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>