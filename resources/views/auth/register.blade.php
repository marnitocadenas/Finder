@extends('layouts.app')
@section('title','Register')
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
                <div class="module-eyebrow">Student Access</div>
                <h1>Create Account</h1>
                <p>Register once to submit lost reports, browse found items, and manage your claims online.</p>
            </div>
            <div class="auth-feature-list">
                <span><i class="fa-solid fa-user-graduate"></i> Student workspace</span>
                <span><i class="fa-solid fa-file-signature"></i> Claim tracking</span>
                <span><i class="fa-solid fa-bell"></i> Notifications</span>
            </div>
        </aside>

        <div class="auth-card auth-card-wide">
            <div class="auth-card-header">
                <span><i class="fa-solid fa-user-plus"></i></span>
                <div>
                    <h2>Student Registration</h2>
                    <p>Use your active email and a strong password.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('register') }}" class="auth-form auth-enhanced-form">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="register-name">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                            <input id="register-name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="Full name" autocomplete="name" required autofocus>
                        </div>
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="register-student-id">Student ID</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-id-card"></i></span>
                            <input id="register-student-id" class="form-control @error('student_id') is-invalid @enderror" name="student_id" value="{{ old('student_id') }}" placeholder="Student ID">
                        </div>
                        @error('student_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="register-email">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                            <input id="register-email" class="form-control @error('email') is-invalid @enderror" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required>
                        </div>
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="register-password">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input id="register-password" class="form-control @error('password') is-invalid @enderror" name="password" type="password" placeholder="Strong password" autocomplete="new-password" data-password-strength data-caps-lock required>
                            <button type="button" class="btn btn-outline-secondary auth-icon-button" data-password-toggle="#register-password" aria-label="Show password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div class="auth-caps-warning" data-caps-lock-warning><i class="fa-solid fa-triangle-exclamation"></i> Caps Lock is on.</div>
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="register-password-confirmation">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input id="register-password-confirmation" class="form-control" name="password_confirmation" type="password" placeholder="Repeat password" autocomplete="new-password" required>
                            <button type="button" class="btn btn-outline-secondary auth-icon-button" data-password-toggle="#register-password-confirmation" aria-label="Show password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
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

                <button class="btn btn-warning w-100" data-loading-text="Creating account">
                    <i class="fa-solid fa-user-plus me-2"></i>Create Account
                </button>

                <div class="auth-switch">
                    <span>Already registered?</span>
                    <a href="{{ route('login') }}">Back to login</a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
