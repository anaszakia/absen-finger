<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template - Login</title>
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
                <h1 class="text-3xl font-bold text-white">Template</h1>
            </div>
            <p class="text-slate-300 text-lg max-w-md leading-relaxed">
                Platform modern untuk mengelola sistem Anda dengan mudah dan efisien
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
            <p>&copy; 2025 Template. All rights reserved.</p>
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
                <h1 class="text-2xl font-bold text-slate-800">Template</h1>
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

                <!-- Divider -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-3 bg-gray-50 text-slate-500">atau</span>
                    </div>
                </div>

                <!-- Social Login -->
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" class="flex items-center justify-center px-4 py-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                        <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span class="text-sm font-medium text-slate-700">Google</span>
                    </button>
                    
                    <button type="button" class="flex items-center justify-center px-4 py-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                        <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                            <path fill="#000000" d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701"/>
                        </svg>
                        <span class="text-sm font-medium text-slate-700">Apple</span>
                    </button>
                </div>

                <!-- Sign Up Link -->
                <div class="text-center mt-6">
                    <p class="text-sm text-slate-600">
                        Belum punya akun? 
                        <a href="/register" class="text-slate-800 font-medium hover:underline">
                            Daftar sekarang
                        </a>
                    </p>
                </div>
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