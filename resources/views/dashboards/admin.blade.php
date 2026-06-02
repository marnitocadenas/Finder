@extends('layouts.app')
@section('title','Admin Overview')
@section('content')
<div class="admin-overview">
    <div class="overview-hero">
        <div>
            <span class="module-eyebrow">Admin console</span>
            <h1>Overview</h1>
            <p>Monitor users, item reports, claims, and recent system activity from one workspace.</p>
        </div>
        <div class="overview-hero-actions">
            <a href="{{ route('admin.reports') }}" class="btn btn-warning">
                <i class="fa-solid fa-chart-column me-1"></i>Open Reports
            </a>
            <a href="{{ route('admin.logs') }}" class="btn btn-light">
                <i class="fa-solid fa-clock-rotate-left me-1"></i>Audit Logs
            </a>
        </div>
    </div>

    @include('partials.stats')

    <div class="row g-3 align-items-stretch">
        <div class="col-xl-8">
            <section class="module-card overview-priority">
                <div class="module-card-header">
                    <div>
                        <span class="module-eyebrow">Needs attention</span>
                        <h2>Today&apos;s Work Queue</h2>
                    </div>
                    <span class="overview-rate">{{ $resolutionRate }}% resolved</span>
                </div>
                <div class="overview-action-grid">
                    @foreach($overview as $item)
                        <a class="overview-action overview-action-{{ $item['tone'] }}" href="{{ $item['route'] }}">
                            <span><i class="fa-solid {{ $item['icon'] }}"></i></span>
                            <strong>{{ $item['value'] }}</strong>
                            <small>{{ $item['label'] }}</small>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="col-xl-4">
            <section class="module-card overview-users">
                <div class="module-card-header">
                    <div>
                        <span class="module-eyebrow">New accounts</span>
                        <h2>Recent Users</h2>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm">Manage</a>
                </div>
                <div class="overview-user-list">
                    @forelse($recentUsers as $user)
                        <div class="overview-user">
                            <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            <div>
                                <strong>{{ $user->name }}</strong>
                                <small>{{ Illuminate\Support\Str::title($user->role) }} &bull; {{ $user->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state compact">
                            <i class="fa-solid fa-user-plus"></i>
                            <p>No users yet.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <section class="module-card">
                <div class="module-card-header">
                    <div>
                        <span class="module-eyebrow">Audit trail</span>
                        <h2>Recent Activity</h2>
                    </div>
                    <a href="{{ route('admin.logs') }}" class="btn btn-light btn-sm">View All</a>
                </div>

                <div class="activity-feed">
                    @forelse($logs as $log)
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                            <div>
                                <strong>{{ $log->user->name ?? 'System' }}</strong>
                                <p>{{ $log->action }}</p>
                            </div>
                            <time>{{ $log->created_at->diffForHumans() }}</time>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="fa-solid fa-clock"></i>
                            <p>No activity yet.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="col-xl-4">
            <section class="module-card module-card-accent">
                <div class="module-card-header">
                    <div>
                        <span class="module-eyebrow">Shortcuts</span>
                        <h2>Manage Records</h2>
                    </div>
                </div>
                <div class="quick-links">
                    <a href="{{ route('admin.users.index') }}"><i class="fa-solid fa-users"></i><span>Users</span></a>
                    <a href="{{ route('admin.lost-items.index') }}"><i class="fa-solid fa-magnifying-glass"></i><span>Lost Items</span></a>
                    <a href="{{ route('admin.found-items.index') }}"><i class="fa-solid fa-box-open"></i><span>Found Items</span></a>
                    <a href="{{ route('admin.claims.index') }}"><i class="fa-solid fa-file-signature"></i><span>Claims</span></a>
                    <a href="{{ route('admin.matches') }}"><i class="fa-solid fa-wand-magic-sparkles"></i><span>Smart Matches</span></a>
                    <a href="{{ route('admin.categories.index') }}"><i class="fa-solid fa-tags"></i><span>Categories</span></a>
                    <a href="{{ route('admin.logs') }}"><i class="fa-solid fa-clock-rotate-left"></i><span>Activity Logs</span></a>
                    <a href="{{ route('admin.settings') }}"><i class="fa-solid fa-gear"></i><span>Settings</span></a>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
