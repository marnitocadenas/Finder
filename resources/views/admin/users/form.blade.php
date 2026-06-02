<div class="user-form-module">
    <div class="user-form-hero">
        <div>
            <span class="module-eyebrow">Account setup</span>
            <h1>{{ $user->exists ? 'Edit User' : 'Create User' }}</h1>
            <p>{{ $user->exists ? 'Update profile details, role access, or set a new password.' : 'Create a new account and assign the right access level.' }}</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-light">
            <i class="fa-solid fa-arrow-left me-1"></i>Back to Users
        </a>
    </div>

    <form method="POST" action="{{ $action }}" class="user-form-card">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="user-form-section">
            <div>
                <h2>Profile Details</h2>
                <p>Keep the user identity clear for reports, claims, and activity logs.</p>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="user-name">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                        <input id="user-name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" placeholder="Full name" required>
                    </div>
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="user-email">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                        <input id="user-email" class="form-control @error('email') is-invalid @enderror" name="email" type="email" value="{{ old('email', $user->email) }}" placeholder="Email address" required>
                    </div>
                    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="user-student-id">Student ID</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-id-card"></i></span>
                        <input id="user-student-id" class="form-control @error('student_id') is-invalid @enderror" name="student_id" value="{{ old('student_id', $user->student_id) }}" placeholder="Optional for non-students">
                    </div>
                    @error('student_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="user-password">{{ $user->exists ? 'New Password' : 'Password' }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input id="user-password" class="form-control @error('password') is-invalid @enderror" name="password" type="password" placeholder="{{ $user->exists ? 'Leave blank to keep current password' : 'Strong password' }}" autocomplete="new-password" data-password-strength data-caps-lock @required(! $user->exists)>
                        <button type="button" class="btn btn-outline-secondary auth-icon-button" data-password-toggle="#user-password" aria-label="Show password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div class="auth-caps-warning" data-caps-lock-warning><i class="fa-solid fa-triangle-exclamation"></i> Caps Lock is on.</div>
                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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
        </div>

        <div class="user-form-section">
            <div>
                <h2>Access Role</h2>
                <p>Choose the workspace this account should use after login.</p>
            </div>
            <div class="role-picker">
                @foreach(['admin' => 'fa-user-shield', 'staff' => 'fa-id-badge', 'student' => 'fa-graduation-cap'] as $role => $icon)
                    <label>
                        <input type="radio" name="role" value="{{ $role }}" @checked(old('role', $user->role ?: 'student') === $role)>
                        <span>
                            <i class="fa-solid {{ $icon }}"></i>
                            <strong>{{ Illuminate\Support\Str::title($role) }}</strong>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="user-form-actions">
            <button class="btn btn-primary" data-loading-text="Saving user">
                <i class="fa-solid fa-floppy-disk me-1"></i>Save User
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
