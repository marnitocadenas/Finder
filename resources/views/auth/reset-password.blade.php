@extends('layouts.app')
@section('title','Reset Password')
@section('content')
<section class="auth-screen">
    <div class="auth-shell auth-shell-compact">
        <aside class="auth-brand-panel">
            <a href="{{ route('home') }}" class="auth-brand">
                <img src="{{ asset('images/tmc-logo.png') }}" alt="Trinidad Municipal College logo">
                <span>
                    <strong>TMC</strong>
                    <small>Lost and Found</small>
                </span>
            </a>
            <div>
                <div class="module-eyebrow">Secure Reset</div>
                <h1>Choose New Password</h1>
                <p>Create a stronger password for {{ $email }} before returning to your account.</p>
            </div>
            <div class="auth-feature-list">
                <span><i class="fa-solid fa-shield-halved"></i> Verified OTP</span>
                <span><i class="fa-solid fa-key"></i> Strong password</span>
            </div>
        </aside>

        <div class="auth-card">
            <div class="auth-card-header">
                <span><i class="fa-solid fa-lock"></i></span>
                <div>
                    <h2>Reset Password</h2>
                    <p>Use a password that is hard to guess and unique to this account.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="auth-form auth-enhanced-form">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="reset-password">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input id="reset-password" class="form-control @error('password') is-invalid @enderror" type="password" name="password" placeholder="Strong password" autocomplete="new-password" data-password-strength data-caps-lock required>
                        <button type="button" class="btn btn-outline-secondary auth-icon-button" data-password-toggle="#reset-password" aria-label="Show password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div class="auth-caps-warning" data-caps-lock-warning><i class="fa-solid fa-triangle-exclamation"></i> Caps Lock is on.</div>
                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="reset-password-confirmation">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input id="reset-password-confirmation" class="form-control" type="password" name="password_confirmation" placeholder="Repeat password" autocomplete="new-password" required>
                        <button type="button" class="btn btn-outline-secondary auth-icon-button" data-password-toggle="#reset-password-confirmation" aria-label="Show password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="auth-password-meter" data-password-meter>
                    <div class="auth-password-meter-bar"><span></span></div>
                    <div class="auth-password-rules">
                        <span data-rule="length"><i class="fa-solid fa-circle"></i> 8+ characters</span>
                        <span data-rule="upper"><i class="fa-solid fa-circle"></i> Uppercase</span>
                        <span data-rule="lower"><i class="fa-solid fa-circle"></i> Lowercase</span>
                        <span data-rule="number"><i class="fa-solid fa-circle"></i> Number</span>
                        <span data-rule="symbol"><i class="fa-solid fa-circle"></i> Symbol</span>
                    </div>
                </div>

                <button class="btn btn-primary w-100" data-loading-text="Resetting password">
                    <i class="fa-solid fa-key me-2"></i>Reset Password
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
