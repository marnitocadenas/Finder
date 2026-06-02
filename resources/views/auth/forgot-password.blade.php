@extends('layouts.app')
@section('title','Forgot Password')
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
                <div class="module-eyebrow">Password Recovery</div>
                <h1>Reset Access</h1>
                <p>Request a verification code using the email linked to your account.</p>
            </div>
            <div class="auth-feature-list">
                <span><i class="fa-solid fa-envelope-circle-check"></i> Email verification</span>
                <span><i class="fa-solid fa-clock"></i> Time-limited OTP</span>
            </div>
        </aside>

        <div class="auth-card">
            <div class="auth-card-header">
                <span><i class="fa-solid fa-key"></i></span>
                <div>
                    <h2>Forgot Password</h2>
                    <p>Students should include their Student ID for verification.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('password.email') }}" class="auth-form auth-enhanced-form">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="recovery-email">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                        <input id="recovery-email" class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required autofocus>
                    </div>
                    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label" for="recovery-student-id">Student ID</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-id-card"></i></span>
                        <input id="recovery-student-id" class="form-control @error('student_id') is-invalid @enderror" name="student_id" value="{{ old('student_id') }}" placeholder="Required for student accounts">
                    </div>
                    @error('student_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <button class="btn btn-primary w-100" data-loading-text="Sending OTP">
                    <i class="fa-solid fa-paper-plane me-2"></i>Send OTP
                </button>

                <div class="auth-switch">
                    <a href="{{ route('login') }}"><i class="fa-solid fa-arrow-left me-2"></i>Back to login</a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
