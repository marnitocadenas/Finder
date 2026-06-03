@extends('layouts.app')
@section('title','User Management')
@section('content')
<div class="users-module">
    <div class="users-hero">
        <div>
            <span class="module-eyebrow">Admin directory</span>
            <h1>User Management</h1>
            <p>Search accounts, review roles, and manage access for administrators, staff, and students.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-warning">
            <i class="fa-solid fa-user-plus me-1"></i>Create User
        </a>
    </div>

    <div class="user-stat-grid">
        @foreach($userStats as $stat)
            <div class="user-stat-card user-stat-{{ $stat['tone'] }}">
                <span><i class="fa-solid {{ $stat['icon'] }}"></i></span>
                <div>
                    <small>{{ $stat['label'] }}</small>
                    <strong>{{ $stat['value'] }}</strong>
                </div>
            </div>
        @endforeach
    </div>

    <section class="users-panel">
        <div class="users-panel-header">
            <div>
                <span class="module-eyebrow">Directory</span>
                <h2>Accounts</h2>
            </div>
            <div class="users-tabs">
                <a class="{{ request('status') ? '' : 'active' }}" href="{{ route('admin.users.index') }}">Active</a>
                <a class="{{ request('status') === 'trashed' ? 'active' : '' }}" href="{{ route('admin.users.index', ['status' => 'trashed']) }}">Deleted</a>
                <a class="{{ request('status') === 'all' ? 'active' : '' }}" href="{{ route('admin.users.index', ['status' => 'all']) }}">All</a>
            </div>
        </div>

        <form class="users-filter">
            <div class="users-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Search by name or email">
            </div>
            <select name="role" class="form-select">
                <option value="">All roles</option>
                @foreach(['admin','staff','student'] as $roleOption)
                    <option value="{{ $roleOption }}" @selected(request('role') === $roleOption)>{{ Illuminate\Support\Str::title($roleOption) }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select">
                <option value="">Active</option>
                <option value="trashed" @selected(request('status') === 'trashed')>Deleted</option>
                <option value="all" @selected(request('status') === 'all')>All</option>
            </select>
            <button class="btn btn-primary">
                <i class="fa-solid fa-filter me-1"></i>Filter
            </button>
        </form>

        <form id="users-bulk-form" class="bulk-toolbar" method="POST" action="{{ route('admin.users.bulk') }}">
            @csrf
            <select name="action" class="form-select form-select-sm" required>
                <option value="">Bulk action</option>
                <option value="delete">Delete selected</option>
                <option value="restore">Restore selected</option>
            </select>
            <button class="btn btn-sm btn-primary" type="submit"><i class="fa-solid fa-bolt me-1"></i>Apply</button>
        </form>

        <div class="users-table-wrap">
            <table class="table users-table excel-users-table align-middle text-center">
                <thead>
                    <tr>
                        <th><input type="checkbox" data-check-all></th>
                        <th class="text-center">User</th>
                        <th>Role</th>
                        <th>Student ID</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php($roleTone = ['admin' => 'primary', 'staff' => 'warning text-dark', 'student' => 'success'][$user->role] ?? 'secondary')
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{ $user->id }}" form="users-bulk-form" @disabled($user->id === auth()->id())></td>
                            <td>
                                <div class="user-identity">
                                    <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        <small>{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-{{ $roleTone }}">{{ Illuminate\Support\Str::title($user->role) }}</span></td>
                            <td class="mono">{{ $user->student_id ?: '-' }}</td>
                            <td>
                                @if($user->trashed())
                                    <span class="badge bg-dark">Deleted</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </td>
                            <td>
                                @if($user->trashed())
                                    <form class="d-inline" method="POST" action="{{ route('admin.users.restore', $user->id) }}">
                                        @csrf @method('PUT')
                                        <button type="button" class="btn btn-sm btn-outline-success" data-confirm-submit data-confirm-title="Restore user" data-confirm-message="Restore this user account?" data-confirm-button="Restore" data-confirm-class="btn-success">
                                            <i class="fa-solid fa-rotate-left me-1"></i>Restore
                                        </button>
                                    </form>
                                @else
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.edit', $user) }}">
                                        <i class="fa-solid fa-pen-to-square me-1"></i>Edit
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form class="d-inline" method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-confirm-submit data-confirm-title="Delete user" data-confirm-message="Move this user to deleted records?" data-confirm-button="Delete">
                                                <i class="fa-solid fa-trash-can me-1"></i>Delete
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="users-empty">
                                    <i class="fa-solid fa-users-slash"></i>
                                    <p>No users found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $users->links() }}
        </div>
    </section>
</div>
@include('partials.confirm-modal')
@endsection
