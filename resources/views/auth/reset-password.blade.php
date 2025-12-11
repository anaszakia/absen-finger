<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template - Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-xl shadow-sm w-full max-w-md fade-in">
        <!-- Logo -->
        <div class="flex justify-center mb-8">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-slate-800 rounded-lg flex items-center justify-center">
                    <div class="w-5 h-5 bg-white rounded"></div>
                </div>
                <h1 class="text-2xl font-bold text-slate-800">Template</h1>
            </div>
        </div>

        <!-- Title -->
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-slate-800 mb-2">Reset Password</h2>
            <p class="text-slate-500">Masukkan password baru Anda</p>
        </div>

        <!-- Reset Password Form -->
        <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
            @csrf
            
            <!-- Email Input (Hidden/Readonly) -->
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email"
                    value="{{ $email }}"
                    class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-slate-50 text-slate-600 focus:outline-none cursor-not-allowed"
                    readonly
                    required
                >
                @error('email')
                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- New Password Input -->
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-2">Password Baru</label>
                <div class="relative">
                    <input 
                        type="password" 
                        id="password" 
                        name="password"
                        class="w-full px-4 py-3 pr-12 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-400 focus:border-transparent transition-all @error('password') border-red-400 @enderror"
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
                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
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
                        placeholder="Ulangi password baru"
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
            </div>

            <!-- Reset Password Button -->
            <button 
                type="submit" 
                class="w-full py-3 px-4 bg-slate-800 hover:bg-slate-900 text-white font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 mt-6"
            >
                Reset Password
            </button>
        </form>
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

        // Confirm password visibility toggle
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('password_confirmation');
        const eyeConfirmIcon = document.getElementById('eyeConfirmIcon');

        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            eyeConfirmIcon.classList.toggle('fa-eye');
            eyeConfirmIcon.classList.toggle('fa-eye-slash');
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
    </script>
</body>
</html>
