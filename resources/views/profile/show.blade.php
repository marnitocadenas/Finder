@php($user = auth()->user())
@php($roleLabel = ucfirst($user->role ?? 'User'))

@extends('layouts.app')
@section('title','Profile')
@section('content')
<div class="profile-module">
    <section class="profile-hero">
        <div class="profile-identity">
            @if($user->profile_photo)
                <img src="{{ asset('storage/'.$user->profile_photo) }}" alt="{{ $user->name }} profile photo">
            @else
                <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
            @endif
            <div>
                <div class="module-eyebrow">Account Settings</div>
                <h1>{{ $user->name }}</h1>
                <p>Keep your contact details and password updated for a secure lost and found account.</p>
            </div>
        </div>
        <div class="profile-badge">
            <i class="fa-solid fa-shield-halved"></i>
            <span>{{ $roleLabel }}</span>
        </div>
    </section>

    <div class="profile-grid">
        <section class="profile-panel">
            <div class="profile-panel-header">
                <span><i class="fa-solid fa-user-pen"></i></span>
                <div>
                    <h2>Profile Details</h2>
                    <p>Update your name, email address, and profile photo.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="profile-form">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label" for="profile-name">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                        <input id="profile-name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="profile-email">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                        <input id="profile-email" class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>
                    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label" for="profile-photo">Profile Photo</label>
                    <input id="profile-photo" class="form-control @error('profile_photo') is-invalid @enderror" type="file" name="profile_photo" accept="image/*">
                    <small class="form-text">Upload a clear image to make your account easier to recognize.</small>
                    @error('profile_photo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <button class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Save Profile
                </button>
            </form>
        </section>

        <aside class="profile-side">
            <section class="profile-summary">
                <div class="profile-summary-photo">
                    @if($user->profile_photo)
                        <img src="{{ asset('storage/'.$user->profile_photo) }}" alt="{{ $user->name }} profile photo">
                    @else
                        <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    @endif
                </div>
                <strong>{{ $user->name }}</strong>
                <small>{{ $user->email }}</small>
                @if($user->student_id)
                    <div class="profile-meta">
                        <i class="fa-solid fa-id-card"></i>
                        <span>{{ $user->student_id }}</span>
                    </div>
                @endif
            </section>

            <section class="profile-panel profile-password-panel">
                <div class="profile-panel-header">
                    <span><i class="fa-solid fa-lock"></i></span>
                    <div>
                        <h2>Change Password</h2>
                        <p>Use a strong password to protect your account.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.password') }}" class="profile-form auth-enhanced-form">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label" for="current-password">Current Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input id="current-password" class="form-control @error('current_password') is-invalid @enderror" type="password" name="current_password" autocomplete="current-password" data-caps-lock required>
                            <button type="button" class="btn btn-outline-secondary auth-icon-button" data-password-toggle="#current-password" aria-label="Show password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div class="auth-caps-warning" data-caps-lock-warning><i class="fa-solid fa-triangle-exclamation"></i> Caps Lock is on.</div>
                        @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="new-password">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                            <input id="new-password" class="form-control @error('password') is-invalid @enderror" type="password" name="password" autocomplete="new-password" data-password-strength data-caps-lock required>
                            <button type="button" class="btn btn-outline-secondary auth-icon-button" data-password-toggle="#new-password" aria-label="Show password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div class="auth-caps-warning" data-caps-lock-warning><i class="fa-solid fa-triangle-exclamation"></i> Caps Lock is on.</div>
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="password-confirmation">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                            <input id="password-confirmation" class="form-control" type="password" name="password_confirmation" autocomplete="new-password" required>
                            <button type="button" class="btn btn-outline-secondary auth-icon-button" data-password-toggle="#password-confirmation" aria-label="Show password">
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

                    <button class="btn btn-warning" data-loading-text="Changing password">
                        <i class="fa-solid fa-key me-2"></i>Change Password
                    </button>
                </form>
            </section>
        </aside>
    </div>
</div>
@endsection
