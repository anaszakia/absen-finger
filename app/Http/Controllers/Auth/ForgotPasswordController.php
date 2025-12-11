<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    /**
     * Display the form to request OTP.
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send OTP code to the given user's email.
     */
    public function sendOTP(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'Email tidak terdaftar dalam sistem.',
        ]);

        // Generate 5 digit OTP
        $otp = str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        
        // Calculate expiry time (30 seconds from now)
        $expiresAt = Carbon::now()->addSeconds(30);

        // Delete old OTP for this email
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Store OTP in database
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'otp' => $otp,
            'expires_at' => $expiresAt,
            'created_at' => Carbon::now(),
        ]);

        // Send OTP via email
        $user = User::where('email', $request->email)->first();
        
        Mail::send('emails.otp', ['otp' => $otp, 'user' => $user], function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Kode OTP Reset Password - Catalyst');
        });

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP telah dikirim ke email Anda.',
            'email' => $request->email,
        ]);
    }

    /**
     * Verify OTP code.
     */
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:5'],
        ]);

        // Get OTP record
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            throw ValidationException::withMessages([
                'otp' => ['Kode OTP tidak ditemukan atau sudah digunakan.'],
            ]);
        }

        // Check if OTP is expired (30 seconds)
        if (Carbon::now()->greaterThan($record->expires_at)) {
            // Delete expired OTP
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            
            throw ValidationException::withMessages([
                'otp' => ['Kode OTP sudah kadaluarsa. Silakan kirim ulang.'],
            ]);
        }

        // Verify OTP
        if ($record->otp !== $request->otp) {
            throw ValidationException::withMessages([
                'otp' => ['Kode OTP tidak valid.'],
            ]);
        }

        // OTP is valid, return success with email for next step
        return response()->json([
            'success' => true,
            'message' => 'Kode OTP valid.',
            'email' => $request->email,
        ]);
    }
}
