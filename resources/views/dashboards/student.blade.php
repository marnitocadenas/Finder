@extends('layouts.app')
@section('title','Student Overview')
@section('content')
<div class="student-overview">
    <div class="student-hero">
        <div>
            <span class="module-eyebrow">Student workspace</span>
            <h1>Overview</h1>
            <p>Track your lost reports, browse available found items, and follow claim updates from one place.</p>
        </div>
        <div class="student-hero-actions">
            <a class="btn btn-warning" href="{{ route('student.lost-items.create') }}">
                <i class="fa-solid fa-plus me-1"></i>Report Lost Item
            </a>
            <a class="btn btn-light" href="{{ route('student.browse') }}">
                <i class="fa-solid fa-box-open me-1"></i>Browse Found
            </a>
        </div>
    </div>

    @include('partials.stats')

    <div class="row g-3 align-items-stretch">
        <div class="col-xl-8">
            <section class="student-panel">
                <div class="student-panel-header">
                    <div>
                        <span class="module-eyebrow">Needs attention</span>
                        <h2>My Activity</h2>
                    </div>
                </div>
                <div class="student-action-grid">
                    @foreach($overview as $item)
                        <a class="student-action student-action-{{ $item['tone'] }}" href="{{ $item['route'] }}">
                            <span><i class="fa-solid {{ $item['icon'] }}"></i></span>
                            <strong>{{ $item['value'] }}</strong>
                            <small>{{ $item['label'] }}</small>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="col-xl-4">
            <section class="student-panel student-panel-accent">
                <div class="student-panel-header">
                    <div>
                        <span class="module-eyebrow">Shortcuts</span>
                        <h2>Quick Actions</h2>
                    </div>
                </div>
                <div class="student-quick-links">
                    <a href="{{ route('student.lost-items.create') }}"><i class="fa-solid fa-plus"></i><span>Report Lost</span></a>
                    <a href="{{ route('student.lost-items.index') }}"><i class="fa-solid fa-list"></i><span>My Reports</span></a>
                    <a href="{{ route('student.browse') }}"><i class="fa-solid fa-box-open"></i><span>Browse Found</span></a>
                    <a href="{{ route('student.claims.index') }}"><i class="fa-solid fa-file-signature"></i><span>My Claims</span></a>
                </div>
            </section>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-7">
            <section class="student-panel">
                <div class="student-panel-header">
                    <div>
                        <span class="module-eyebrow">Reports</span>
                        <h2>Recent Lost Reports</h2>
                    </div>
                    <a href="{{ route('student.lost-items.index') }}" class="btn btn-light btn-sm">View All</a>
                </div>
                <div class="student-list">
                    @forelse($recentLostReports as $item)
                        <a class="student-list-item" href="{{ route('student.lost-items.show', $item) }}">
                            @if($item->image)
                                <img class="lazy-thumb skeleton" loading="lazy" decoding="async" src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->title }}" onload="this.classList.remove('skeleton')">
                            @else
                                <span><i class="fa-solid fa-magnifying-glass"></i></span>
                            @endif
                            <div>
                                <strong>{{ $item->title }}</strong>
                                <small>
                                    <i class="fa-solid {{ $item->category->icon ?? 'fa-tag' }}"></i>
                                    {{ $item->category->name ?? 'Uncategorized' }} &bull; {{ $item->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <x-status :status="$item->status" />
                        </a>
                    @empty
                        <div class="empty-state">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <p>No lost reports submitted yet.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="col-xl-5">
            <section class="student-panel">
                <div class="student-panel-header">
                    <div>
                        <span class="module-eyebrow">Claims</span>
                        <h2>Recent Claims</h2>
                    </div>
                    <a href="{{ route('student.claims.index') }}" class="btn btn-light btn-sm">View Claims</a>
                </div>
                <div class="student-claim-list">
                    @forelse($recentClaims as $claim)
                        <a class="student-claim-item" href="{{ route('student.claims.show', $claim) }}">
                            <span><i class="fa-solid fa-file-signature"></i></span>
                            <div>
                                <strong>{{ $claim->foundItem->title ?? 'Found item' }}</strong>
                                <small>{{ $claim->created_at->diffForHumans() }}</small>
                            </div>
                            <x-status :status="$claim->status" />
                        </a>
                    @empty
                        <div class="empty-state">
                            <i class="fa-solid fa-file-signature"></i>
                            <p>No claims submitted yet.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <section class="student-panel">
        <div class="student-panel-header">
            <div>
                <span class="module-eyebrow">Alerts</span>
                <h2>Latest Notifications</h2>
            </div>
            <a href="{{ route('notifications') }}" class="btn btn-light btn-sm">Open Notifications</a>
        </div>
        <div class="student-notification-grid">
            @forelse($notifications as $notification)
                <a class="student-notification {{ $notification->is_read ? '' : 'is-unread' }}" href="{{ $notification->link ?: route('notifications') }}">
                    <i class="fa-solid fa-bell"></i>
                    <div>
                        <strong>{{ $notification->title }}</strong>
                        <p>{{ $notification->message }}</p>
                    </div>
                    <time>{{ $notification->created_at->diffForHumans() }}</time>
                </a>
            @empty
                <div class="empty-state">
                    <i class="fa-solid fa-bell"></i>
                    <p>No notifications yet.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
