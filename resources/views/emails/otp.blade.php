<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #334155;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .logo-inner {
            width: 25px;
            height: 25px;
            background: #1e293b;
            border-radius: 4px;
        }
        .content {
            padding: 40px 30px;
        }
        .otp-box {
            background: #f1f5f9;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 48px;
            font-weight: bold;
            color: #1e293b;
            letter-spacing: 10px;
            margin: 10px 0;
        }
        .timer-warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .footer {
            background: #f8fafc;
            padding: 30px;
            text-align: center;
            font-size: 14px;
            color: #64748b;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #1e293b;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
        h1 {
            margin: 0;
            font-size: 24px;
        }
        p {
            margin: 10px 0;
        }
        .text-muted {
            color: #64748b;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="email-card">
            <!-- Header -->
            <div class="header">
                <div class="logo">
                    <div class="logo-inner"></div>
                </div>
                <h1>Kode Verifikasi OTP</h1>
            </div>

            <!-- Content -->
            <div class="content">
                <p>Halo <strong>{{ $user->name }}</strong>,</p>
                
                <p>Kami menerima permintaan untuk mereset password akun Anda. Gunakan kode OTP berikut untuk melanjutkan proses reset password:</p>

                <!-- OTP Box -->
                <div class="otp-box">
                    <p class="text-muted" style="margin-bottom: 5px;">Kode OTP Anda</p>
                    <div class="otp-code">{{ $otp }}</div>
                    <p class="text-muted" style="margin-top: 5px;">Masukkan kode ini di halaman verifikasi</p>
                </div>

                <!-- Timer Warning -->
                <div class="timer-warning">
                    <strong>⏱️ Penting!</strong> Kode OTP ini hanya berlaku selama <strong>30 detik</strong>. Setelah waktu habis, Anda perlu meminta kode baru.
                </div>

                <p>Jika Anda tidak meminta reset password, abaikan email ini. Akun Anda tetap aman.</p>

                <p class="text-muted" style="margin-top: 30px;">
                    <strong>Tips Keamanan:</strong><br>
                    • Jangan bagikan kode OTP ini kepada siapa pun<br>
                    • Kami tidak akan pernah meminta kode OTP melalui telepon atau chat<br>
                    • Pastikan Anda mengakses situs resmi kami
                </p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>&copy; {{ date('Y') }} Template. All rights reserved.</p>
                <p class="text-muted">Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
            </div>
        </div>
    </div>
</body>
</html>
