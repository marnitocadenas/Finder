<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\AuthPasswordRules;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Models\User;
class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm(): View { return view('auth.forgot-password'); }
    public function showOtpForm(Request $request): View
    {
        abort_unless($request->session()->has('password_reset_email'), 403);
        return view('auth.verify-password-otp', ['email' => $request->session()->get('password_reset_email')]);
    }

    public function showResetForm(Request $request): View
    {
        abort_unless($request->session()->get('password_reset_verified'), 403);
        return view('auth.reset-password', ['email' => $request->session()->get('password_reset_email')]);
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $throttleKey = 'password-reset|'.Str::lower((string) $request->input('email')).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            throw ValidationException::withMessages([
                'email' => 'Too many OTP requests. Please try again in '.RateLimiter::availableIn($throttleKey).' seconds.',
            ]);
        }

        $data = $request->validate([
            'email' => ['required', 'email'],
            'student_id' => ['nullable', 'string', 'max:20'],
        ]);
        RateLimiter::hit($throttleKey, 600);

        $user = User::where('email', $data['email'])->first();
        if (! $user) {
            return back()->withErrors(['email' => 'We could not find an account with that email address.'])->withInput();
        }

        if ($user->role === 'student' && $user->student_id !== ($data['student_id'] ?? null)) {
            return back()->withErrors(['student_id' => 'The Student ID does not match this email address.'])->withInput();
        }

        $otp = (string) random_int(100000, 999999);
        DB::table('password_reset_otps')->where('email', $user->email)->delete();
        DB::table('password_reset_otps')->insert([
            'email' => $user->email,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Mail::send('emails.password-otp', ['user' => $user, 'otp' => $otp], function ($message) use ($user) {
            $message->to($user->email)->subject('Your TMC password reset OTP');
        });

        $request->session()->put('password_reset_email', $user->email);
        $request->session()->forget('password_reset_verified');
        ActivityLog::create(['user_id' => $user->id, 'action' => 'Requested password reset OTP', 'target_type' => 'Authentication', 'target_id' => $user->id, 'ip_address' => $request->ip()]);

        return redirect()->route('password.otp')->with('success', 'We sent a 6-digit OTP to your email address.');
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $data = $request->validate(['otp' => ['required', 'digits:6']]);
        $email = $request->session()->get('password_reset_email');
        abort_unless($email, 403);

        $record = DB::table('password_reset_otps')
            ->where('email', $email)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $record || now()->greaterThan($record->expires_at)) {
            return redirect()->route('password.request')->withErrors(['email' => 'Your OTP expired. Please request a new one.']);
        }

        if ($record->attempts >= 5) {
            DB::table('password_reset_otps')->where('id', $record->id)->delete();
            return redirect()->route('password.request')->withErrors(['email' => 'Too many incorrect OTP attempts. Please request a new one.']);
        }

        if (! Hash::check($data['otp'], $record->otp_hash)) {
            DB::table('password_reset_otps')->where('id', $record->id)->increment('attempts');
            return back()->withErrors(['otp' => 'The OTP code is incorrect.'])->withInput();
        }

        DB::table('password_reset_otps')->where('id', $record->id)->update(['verified_at' => now(), 'updated_at' => now()]);
        $request->session()->put('password_reset_verified', true);

        return redirect()->route('password.reset')->with('success', 'OTP verified. You can now choose a new password.');
    }

    public function reset(Request $request): RedirectResponse
    {
        abort_unless($request->session()->get('password_reset_verified'), 403);
        $data = $request->validate(['password' => AuthPasswordRules::rules()]);
        $email = $request->session()->get('password_reset_email');
        $user = User::where('email', $email)->firstOrFail();

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'remember_token' => Str::random(60),
        ])->save();

        DB::table('password_reset_otps')->where('email', $email)->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->forget(['password_reset_email', 'password_reset_verified']);
        event(new PasswordReset($user));
        ActivityLog::create(['user_id' => $user->id, 'action' => 'Reset password', 'target_type' => 'Authentication', 'target_id' => $user->id, 'ip_address' => $request->ip()]);

        return redirect()->route('login')->with('success', 'Your password has been reset. You may now login.');
    }
}
