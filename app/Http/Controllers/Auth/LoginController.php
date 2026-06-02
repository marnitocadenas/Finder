<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Concerns\LogsActivity;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    use LogsActivity;

    public function showForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many login attempts. Please try again in '.RateLimiter::availableIn($throttleKey).' seconds.',
            ]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            $this->logAction($request, 'Logged in');

            return redirect()->intended($request->user()->dashboardRoute())->with('success', 'Welcome back, '.$request->user()->name.'.');
        }

        RateLimiter::hit($throttleKey, 60);
        ActivityLog::create([
            'user_id' => null,
            'action' => 'Failed login attempt for '.$credentials['email'],
            'target_type' => 'Authentication',
            'target_id' => null,
            'ip_address' => $request->ip(),
        ]);

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->logAction($request, 'Logged out');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been logged out.');
    }

    private function throttleKey(Request $request): string
    {
        return Str::lower((string) $request->input('email')).'|'.$request->ip();
    }
}
