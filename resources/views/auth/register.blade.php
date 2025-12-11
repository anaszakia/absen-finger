<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template - Sign Up</title>
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
        .password-strength {
            height: 3px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        .strength-weak { background-color: #ef4444; width: 33%; }
        .strength-medium { background-color: #f59e0b; width: 66%; }
        .strength-strong { background-color: #10b981; width: 100%; }
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
                Bergabunglah dengan ribuan pengguna yang telah mempercayai platform kami
            </p>
        </div>

        <!-- Illustration -->
        <div class="relative z-10 flex items-center justify-center my-8">
            <div class="float-animation">
                <svg class="w-96 h-96" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- User group illustration -->
                    <circle cx="160" cy="180" r="35" fill="#94a3b8" opacity="0.8"/>
                    <circle cx="240" cy="180" r="35" fill="#94a3b8" opacity="0.8"/>
                    <circle cx="200" cy="140" r="40" fill="white" opacity="0.9"/>
                    
                    <!-- Bodies -->
                    <ellipse cx="160" cy="260" rx="45" ry="55" fill="#94a3b8" opacity="0.8"/>
                    <ellipse cx="240" cy="260" rx="45" ry="55" fill="#94a3b8" opacity="0.8"/>
                    <ellipse cx="200" cy="240" rx="50" ry="60" fill="white" opacity="0.9"/>
                    
                    <!-- Plus icon -->
                    <circle cx="300" cy="160" r="30" fill="#10b981" opacity="0.9"/>
                    <rect x="290" y="150" width="20" height="4" rx="2" fill="white"/>
                    <rect x="298" y="142" width="4" height="20" rx="2" fill="white"/>
                </svg>
            </div>
        </div>

        <!-- Footer text -->
        <div class="relative z-10 text-slate-400 text-sm fade-in">
            <p>&copy; 2025 Template. All rights reserved.</p>
        </div>
    </div>

    <!-- Right Side - Register Form -->
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
                <h2 class="text-3xl font-bold text-slate-800 mb-2">Buat Akun</h2>
                <p class="text-slate-500">Daftar untuk memulai perjalanan Anda</p>
            </div>

            <!-- Registration Form -->
            <form method="POST" action="{{ route('register.submit') }}" id="registerForm" class="space-y-4">
                @csrf
                <!-- Full Name Input -->
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-2">Nama Lengkap</label>
                    <input 
                        type="text" 
                        id="name"
                        name="name" 
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-400 focus:border-transparent transition-all @error('name') border-red-500 @else border-slate-200 @enderror"
                        placeholder="Masukkan nama lengkap"
                        value="{{ old('name') }}"
                        required
                    >
                    @error('name')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @else
                        <span class="text-red-500 text-sm mt-1 hidden" id="nameError">Nama lengkap harus diisi</span>
                    @enderror
                </div>

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                    <input 
                        type="email" 
                        id="email"
                        name="email" 
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-400 focus:border-transparent transition-all @error('email') border-red-500 @else border-slate-200 @enderror"
                        placeholder="nama@email.com"
                        value="{{ old('email') }}"
                        required
                    >
                    @error('email')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @else
                        <span class="text-red-500 text-sm mt-1 hidden" id="emailError">Email tidak valid</span>
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
                            class="w-full px-4 py-3 pr-12 border rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-400 focus:border-transparent transition-all @error('password') border-red-500 @else border-slate-200 @enderror"
                            placeholder="Minimal 8 karakter"
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
                    <!-- Password Strength Indicator -->
                    <div class="mt-2">
                        <div class="flex justify-between text-xs text-slate-500 mb-1">
                            <span>Kekuatan Password</span>
                            <span id="strengthText">Lemah</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-1">
                            <div class="password-strength strength-weak" id="strengthBar"></div>
                        </div>
                    </div>
                    @error('password')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @else
                        <span class="text-red-500 text-sm mt-1 hidden" id="passwordError">Password minimal 8 karakter</span>
                    @enderror
                </div>

                <!-- Confirm Password Input -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-2">Konfirmasi Password</label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password_confirmation"
                            name="password_confirmation" 
                            class="w-full px-4 py-3 pr-12 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-400 focus:border-transparent transition-all"
                            placeholder="Ulangi password"
                            required
                        >
                        <button 
                            type="button" 
                            id="toggleConfirmPassword" 
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                        >
                            <i class="fas fa-eye text-sm" id="eyeConfirmIcon"></i>
                        </button>
                    </div>
                    <span class="text-red-500 text-sm mt-1 hidden" id="confirmPasswordError">Password tidak cocok</span>
                </div>

                <!-- Terms and Conditions -->
                <div class="flex items-start pt-2">
                    <input 
                        id="terms" 
                        name="terms" 
                        type="checkbox" 
                        class="h-4 w-4 text-slate-800 focus:ring-slate-400 border-slate-300 rounded mt-1"
                        required
                    >
                    <label for="terms" class="ml-3 block text-sm text-slate-700">
                        Saya setuju dengan 
                        <a href="#" class="text-slate-800 font-medium hover:underline">Syarat & Ketentuan</a> 
                        dan 
                        <a href="#" class="text-slate-800 font-medium hover:underline">Kebijakan Privasi</a>
                    </label>
                </div>

                <!-- Sign Up Button -->
                <button 
                    type="submit" 
                    class="w-full py-3 px-4 bg-slate-800 hover:bg-slate-900 text-white font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
                >
                    Buat Akun
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

                <!-- Sign In Link -->
                <div class="text-center mt-6">
                    <p class="text-sm text-slate-600">
                        Sudah punya akun? 
                        <a href="/login" class="text-slate-800 font-medium hover:underline">
                            Masuk sekarang
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Password visibility toggle for password field
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            if (type === 'password') {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            } else {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        });

        // Password visibility toggle for confirm password field
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('password_confirmation');
        const eyeConfirmIcon = document.getElementById('eyeConfirmIcon');

        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            
            if (type === 'password') {
                eyeConfirmIcon.classList.remove('fa-eye-slash');
                eyeConfirmIcon.classList.add('fa-eye');
            } else {
                eyeConfirmIcon.classList.remove('fa-eye');
                eyeConfirmIcon.classList.add('fa-eye-slash');
            }
        });

        // Password strength checker
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            strengthBar.className = 'password-strength';
            
            if (strength <= 1) {
                strengthBar.classList.add('strength-weak');
                strengthText.textContent = 'Lemah';
            } else if (strength <= 2) {
                strengthBar.classList.add('strength-medium');
                strengthText.textContent = 'Sedang';
            } else {
                strengthBar.classList.add('strength-strong');
                strengthText.textContent = 'Kuat';
            }
        });

        // Confirm password validation on blur
        confirmPasswordInput.addEventListener('blur', function() {
            if (this.value && passwordInput.value && this.value !== passwordInput.value) {
                alert('Password tidak cocok!');
            }
        });

        // Form submission handler - Client-side validation only
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            const terms = document.getElementById('terms').checked;
            
            // Only validate password confirmation
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                return false;
            }

            if (!terms) {
                e.preventDefault();
                alert('Harap setujui Syarat & Ketentuan dan Kebijakan Privasi');
                return false;
            }

            // Show loading state
            const button = this.querySelector('button[type="submit"]');
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
            button.disabled = true;
            
            // Let the form submit normally to Laravel
            return true;
        });

        // Social registration handlers (buttons are now in grid)
        const socialButtons = document.querySelectorAll('button[type="button"]');
        socialButtons.forEach((btn, index) => {
            if (btn.querySelector('svg')) {
                btn.addEventListener('click', function() {
                    console.log(index === 0 ? 'Google registration clicked' : 'Apple registration clicked');
                });
            }
        });

    </script>
</body>
</html>