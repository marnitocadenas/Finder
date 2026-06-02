@extends('layouts.app')
@section('title','Login')
@section('content')
<section class="auth-screen">
    <div class="auth-shell">
        <aside class="auth-brand-panel">
            <a href="{{ route('home') }}" class="auth-brand">
                <img src="{{ asset('images/tmc-logo.png') }}" alt="Trinidad Municipal College logo">
                <span>
                    <strong>TMC</strong>
                    <small>Lost and Found</small>
                </span>
            </a>
            <div>
                <div class="module-eyebrow">Secure Access</div>
                <h1>Welcome Back</h1>
                <p>Sign in to report lost belongings, browse found items, and track your claims from one campus system.</p>
            </div>
            <div class="auth-feature-list">
                <span><i class="fa-solid fa-shield-halved"></i> Account protected</span>
                <span><i class="fa-solid fa-bell"></i> Claim updates</span>
                <span><i class="fa-solid fa-box-open"></i> Found item browsing</span>
            </div>
        </aside>

        <div class="auth-card">
            <div class="auth-card-header">
                <span><i class="fa-solid fa-right-to-bracket"></i></span>
                <div>
                    <h2>Login</h2>
                    <p>Use your registered email and password.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('login') }}" class="auth-form auth-enhanced-form">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="login-email">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                        <input id="login-email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" type="email" placeholder="you@example.com" autocomplete="email" required autofocus>
                    </div>
                    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="login-password">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input id="login-password" name="password" class="form-control @error('password') is-invalid @enderror" type="password" placeholder="Enter password" autocomplete="current-password" data-caps-lock required>
                        <button type="button" class="btn btn-outline-secondary auth-icon-button" data-password-toggle="#login-password" aria-label="Show password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div class="auth-caps-warning" data-caps-lock-warning><i class="fa-solid fa-triangle-exclamation"></i> Caps Lock is on.</div>
                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="auth-form-row">
                    <div class="auth-remember-group">
                        <label class="auth-check">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                        <small class="auth-helper-text">Keep me signed in on this device.</small>
                    </div>
                    <a href="{{ route('password.request') }}">Forgot password?</a>
                </div>

                <button class="btn btn-primary w-100" data-loading-text="Signing in">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>Login
                </button>

                <div class="auth-switch">
                    <span>No account yet?</span>
                    <a href="{{ route('register') }}">Register as student</a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
