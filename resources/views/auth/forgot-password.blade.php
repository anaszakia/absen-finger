<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template - Lupa Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        .otp-input {
            width: 3.5rem;
            height: 3.5rem;
            font-size: 1.5rem;
            text-align: center;
        }
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

        <!-- Step 1: Email Form -->
        <div id="emailStep">
            <!-- Title -->
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-slate-800 mb-2">Lupa Password?</h2>
                <p class="text-slate-500">Masukkan email Anda untuk menerima kode OTP</p>
            </div>

            <!-- Email Form -->
            <form id="emailForm" class="space-y-6">
                @csrf
                
                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email"
                        class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-400 focus:border-transparent transition-all"
                        placeholder="nama@email.com"
                        required
                        autofocus
                    >
                    <span id="emailError" class="text-red-500 text-sm mt-1 hidden"></span>
                </div>

                <!-- Send OTP Button -->
                <button 
                    type="submit" 
                    id="sendOtpBtn"
                    class="w-full py-3 px-4 bg-slate-800 hover:bg-slate-900 text-white font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
                >
                    Kirim Kode OTP
                </button>

                <!-- Back to Login Link -->
                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-slate-600 hover:text-slate-800 inline-flex items-center transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali ke Login
                    </a>
                </div>
            </form>
        </div>

        <!-- Step 2: OTP Verification Form -->
        <div id="otpStep" class="hidden">
            <!-- Title -->
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-slate-800 mb-2">Verifikasi Kode OTP</h2>
                <p class="text-slate-500">Masukkan 5 digit kode yang dikirim ke <span id="sentEmail" class="font-semibold"></span></p>
            </div>

            <!-- Timer Display -->
            <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg text-center">
                <p class="text-sm text-amber-800">Kode akan kadaluarsa dalam</p>
                <p class="text-3xl font-bold text-amber-900 mt-2" id="timer">00:30</p>
            </div>

            <!-- OTP Form -->
            <form id="otpForm" class="space-y-6">
                @csrf
                <input type="hidden" id="otpEmail" name="email">
                
                <!-- OTP Inputs -->
                <div class="flex justify-center gap-3">
                    <input type="text" maxlength="1" class="otp-input border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-400" id="otp1" data-index="0">
                    <input type="text" maxlength="1" class="otp-input border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-400" id="otp2" data-index="1">
                    <input type="text" maxlength="1" class="otp-input border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-400" id="otp3" data-index="2">
                    <input type="text" maxlength="1" class="otp-input border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-400" id="otp4" data-index="3">
                    <input type="text" maxlength="1" class="otp-input border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-400" id="otp5" data-index="4">
                </div>
                <span id="otpError" class="text-red-500 text-sm mt-2 text-center hidden"></span>

                <!-- Verify Button -->
                <button 
                    type="submit" 
                    id="verifyOtpBtn"
                    class="w-full py-3 px-4 bg-slate-800 hover:bg-slate-900 text-white font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
                >
                    Verifikasi Kode
                </button>

                <!-- Resend OTP Link -->
                <div class="text-center">
                    <button type="button" id="resendOtp" class="text-sm text-slate-600 hover:text-slate-800 transition-colors">
                        Kirim ulang kode
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let countdownTimer;
        let timeLeft = 30;

        // Email Form Submission
        document.getElementById('emailForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const sendBtn = document.getElementById('sendOtpBtn');
            const emailError = document.getElementById('emailError');
            
            // Reset error
            emailError.classList.add('hidden');
            
            // Disable button
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim...';
            
            try {
                const response = await fetch('{{ route("password.sendOTP") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ email })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Switch to OTP step
                    document.getElementById('emailStep').classList.add('hidden');
                    document.getElementById('otpStep').classList.remove('hidden');
                    document.getElementById('sentEmail').textContent = email;
                    document.getElementById('otpEmail').value = email;
                    
                    // Focus first OTP input
                    document.getElementById('otp1').focus();
                    
                    // Start countdown
                    startCountdown();
                } else {
                    emailError.textContent = data.message || 'Terjadi kesalahan';
                    emailError.classList.remove('hidden');
                }
            } catch (error) {
                const errorData = await error.response?.json();
                emailError.textContent = errorData?.message || 'Email tidak terdaftar';
                emailError.classList.remove('hidden');
            } finally {
                sendBtn.disabled = false;
                sendBtn.innerHTML = 'Kirim Kode OTP';
            }
        });

        // OTP Input Auto-focus
        const otpInputs = document.querySelectorAll('.otp-input');
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                if (e.target.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });
            
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
            
            // Only allow numbers
            input.addEventListener('keypress', function(e) {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });
        });

        // OTP Form Submission
        document.getElementById('otpForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const otp = Array.from(otpInputs).map(input => input.value).join('');
            const email = document.getElementById('otpEmail').value;
            const verifyBtn = document.getElementById('verifyOtpBtn');
            const otpError = document.getElementById('otpError');
            
            if (otp.length !== 5) {
                otpError.textContent = 'Masukkan 5 digit kode OTP';
                otpError.classList.remove('hidden');
                return;
            }
            
            // Reset error
            otpError.classList.add('hidden');
            
            // Disable button
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memverifikasi...';
            
            try {
                const response = await fetch('{{ route("password.verifyOTP") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ email, otp })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Clear countdown
                    clearInterval(countdownTimer);
                    
                    // Redirect to reset password page with email
                    window.location.href = '{{ route("password.reset", ["token" => "verified"]) }}?email=' + encodeURIComponent(email);
                } else {
                    otpError.textContent = data.message || 'Kode OTP tidak valid';
                    otpError.classList.remove('hidden');
                }
            } catch (error) {
                otpError.textContent = 'Kode OTP tidak valid atau sudah kadaluarsa';
                otpError.classList.remove('hidden');
            } finally {
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = 'Verifikasi Kode';
            }
        });

        // Countdown Timer
        function startCountdown() {
            timeLeft = 30;
            updateTimerDisplay();
            
            countdownTimer = setInterval(() => {
                timeLeft--;
                updateTimerDisplay();
                
                if (timeLeft <= 0) {
                    clearInterval(countdownTimer);
                    document.getElementById('otpError').textContent = 'Kode OTP telah kadaluarsa. Silakan kirim ulang.';
                    document.getElementById('otpError').classList.remove('hidden');
                    document.getElementById('verifyOtpBtn').disabled = true;
                }
            }, 1000);
        }

        function updateTimerDisplay() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            document.getElementById('timer').textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        // Resend OTP
        document.getElementById('resendOtp').addEventListener('click', async function() {
            const email = document.getElementById('otpEmail').value;
            this.disabled = true;
            this.textContent = 'Mengirim ulang...';
            
            try {
                const response = await fetch('{{ route("password.sendOTP") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ email })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Reset OTP inputs
                    otpInputs.forEach(input => input.value = '');
                    document.getElementById('otp1').focus();
                    
                    // Reset error
                    document.getElementById('otpError').classList.add('hidden');
                    document.getElementById('verifyOtpBtn').disabled = false;
                    
                    // Restart countdown
                    clearInterval(countdownTimer);
                    startCountdown();
                }
            } catch (error) {
                alert('Gagal mengirim ulang kode');
            } finally {
                this.disabled = false;
                this.textContent = 'Kirim ulang kode';
            }
        });
    </script>
</body>
</html>