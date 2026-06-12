@extends('layouts.app')
@section('title','Notifications')
@section('content')
<div class="notifications-module">
    <div class="notifications-hero">
        <div>
            <span class="module-eyebrow">Message center</span>
            <h1>Notifications</h1>
            <p>Review claim updates, match alerts, and system messages related to your lost and found activity.</p>
        </div>
        <form method="POST" action="{{ route('notifications.readAll') }}" id="mark-all-read-form">
            @csrf
            <button class="btn btn-light" type="submit">
                <i class="fa-solid fa-check-double me-1"></i>Mark All Read
            </button>
        </form>
    </div>

    <div class="notification-stat-grid">
        @foreach($notificationStats as $stat)
            <div class="notification-stat-card notification-stat-{{ $stat['tone'] }}">
                <span><i class="fa-solid {{ $stat['icon'] }}"></i></span>
                <div>
                    <small>{{ $stat['label'] }}</small>
                    <strong>{{ $stat['value'] }}</strong>
                </div>
            </div>
        @endforeach
    </div>

    <section class="notifications-panel">
        <div class="notifications-panel-header">
            <div>
                <span class="module-eyebrow">Inbox</span>
                <h2>Recent Alerts</h2>
            </div>
            <span class="notification-result-count">{{ $notifications->total() }} messages</span>
        </div>

        <div class="notification-list">
            @forelse($notifications as $notification)
                @php
                    $typeMap = [
                        'match_alert' => ['icon' => 'fa-wand-magic-sparkles', 'tone' => 'success'],
                        'claim_update' => ['icon' => 'fa-file-signature', 'tone' => 'primary'],
                    ];
                    $meta = $typeMap[$notification->type] ?? ['icon' => 'fa-bell', 'tone' => 'warning'];
                @endphp
                <a class="notification-item {{ $notification->is_read ? '' : 'is-unread' }}" href="{{ $notification->link ?: '#' }}" data-notification-id="{{ $notification->id }}">
                    <span class="notification-icon notification-icon-{{ $meta['tone'] }}">
                        <i class="fa-solid {{ $meta['icon'] }}"></i>
                    </span>
                    <div class="notification-content">
                        <div>
                            <strong>{{ $notification->title }}</strong>
                            @unless($notification->is_read)
                                <em data-notification-status>Unread</em>
                            @else
                                <em data-notification-status class="text-success">Read</em>
                            @endunless
                        </div>
                        <p>{{ $notification->message }}</p>
                    </div>
                    <time>{{ $notification->created_at->diffForHumans() }}</time>
                </a>
            @empty
                <div class="notifications-empty">
                    <i class="fa-solid fa-bell"></i>
                    <p>No notifications yet.</p>
                </div>
            @endforelse
        </div>

        <div class="notifications-pagination">
            {{ $notifications->links() }}
        </div>
    </section>
</div>
@endsection
