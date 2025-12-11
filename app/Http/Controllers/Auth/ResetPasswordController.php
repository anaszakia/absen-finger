<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class ResetPasswordController extends Controller
{
    /**
     * Display the password reset view (after OTP verification).
     */
    public function showResetForm(Request $request, $token)
    {
        // Token "verified" means OTP has been verified
        if ($token !== 'verified' || !$request->email) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Link tidak valid atau sudah kadaluarsa.']);
        }

        return view('auth.reset-password', [
            'email' => $request->email
        ]);
    }

    /**
     * Handle an incoming password reset request (after OTP verification).
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.exists' => 'Email tidak ditemukan.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // Find the user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Email tidak ditemukan.'],
            ]);
        }

        // Update password
        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        // Delete OTP record after successful reset
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Redirect to login with success message
        return redirect()->route('login')
            ->with('status', 'Password Anda berhasil direset! Silakan login dengan password baru.');
    }
}
