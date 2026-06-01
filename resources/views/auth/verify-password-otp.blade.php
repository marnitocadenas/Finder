@extends('layouts.app')
@section('title','Verify OTP')
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
                <div class="module-eyebrow">Verification</div>
                <h1>Check Your Email</h1>
                <p>Use the 6-digit code we sent to finish your password reset request.</p>
            </div>
            <div class="auth-feature-list">
                <span><i class="fa-solid fa-envelope-circle-check"></i> Sent to your email</span>
                <span><i class="fa-solid fa-clock"></i> Expires in 10 minutes</span>
            </div>
        </aside>

        <div class="auth-card">
            <div class="auth-card-header">
                <span><i class="fa-solid fa-shield-halved"></i></span>
                <div>
                    <h2>Verify OTP</h2>
                    <p>Enter the 6-digit code sent to {{ $email }}.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('password.otp.verify') }}" class="auth-form">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="otp-code">Verification Code</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                        <input id="otp-code" class="form-control" name="otp" inputmode="numeric" maxlength="6" placeholder="6-digit OTP" required autofocus>
                    </div>
                </div>

                <button class="btn btn-primary w-100 mt-2">
                    <i class="fa-solid fa-circle-check me-2"></i>Verify OTP
                </button>

                <div class="auth-resend-card mt-3" style="margin-top:1rem;">
                    <div>
                        <strong>Didn't receive the code?</strong>
                        <a class="auth-resend-inline" href="{{ route('password.request') }}" style="color:#1A3C6E;text-decoration:none;font-weight:800;">
                            <i class="fa-solid fa-rotate-right" style="color:#1A3C6E;"></i>
                            <span style="color:#1A3C6E;">Request new code</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
