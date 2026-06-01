@php($user = auth()->user())
@php($roleLabel = ucfirst($user->role ?? 'User'))

@extends('layouts.app')
@section('title','Profile')
@section('content')
<div class="profile-module">
    <section class="profile-hero">
        <div class="profile-identity">
            @if($user->profile_photo)
                <img class="lazy-thumb skeleton rounded-circle" loading="lazy" decoding="async" src="{{ asset('storage/'.$user->profile_photo) }}" alt="{{ $user->name }} profile photo" onload="this.classList.remove('skeleton')">
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
                        <input id="profile-name" class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="profile-email">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                        <input id="profile-email" class="form-control" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="profile-photo">Profile Photo</label>
                    <input id="profile-photo" class="form-control" type="file" name="profile_photo" accept="image/*">
                    <small class="form-text">Upload a clear image to make your account easier to recognize.</small>
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
                        <img class="lazy-thumb skeleton rounded-circle" loading="lazy" decoding="async" src="{{ asset('storage/'.$user->profile_photo) }}" alt="{{ $user->name }} profile photo" onload="this.classList.remove('skeleton')">
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

                <form method="POST" action="{{ route('profile.password') }}" class="profile-form">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label" for="current-password">Current Password</label>
                        <input id="current-password" class="form-control" type="password" name="current_password" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="new-password">New Password</label>
                        <input id="new-password" class="form-control" type="password" name="password" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="password-confirmation">Confirm Password</label>
                        <input id="password-confirmation" class="form-control" type="password" name="password_confirmation" required>
                    </div>

                    <button class="btn btn-warning">
                        <i class="fa-solid fa-key me-2"></i>Change Password
                    </button>
                </form>
            </section>
        </aside>
    </div>
</div>
@endsection
