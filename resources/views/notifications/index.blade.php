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
        <form method="POST" action="{{ route('notifications.readAll') }}" id="mark-all-read-form" data-ajax>
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
            <div class="d-flex align-items-center gap-2">
                <input type="checkbox" id="notification-select-all" class="form-check-input" data-notification-select-all>
                <label for="notification-select-all" class="form-check-label fw-bold" style="cursor:pointer;font-size:.85rem;">Select All</label>
            </div>
            <div class="d-flex align-items-center gap-2">
                <form method="POST" action="{{ route('notifications.bulkDelete') }}" id="notification-bulk-delete-form" class="d-none" data-ajax>
                    @csrf
                    <input type="hidden" name="ids" id="notification-bulk-ids">
                </form>
                <form method="POST" action="{{ route('notifications.bulkRead') }}" id="notification-bulk-read-form" class="d-none" data-ajax>
                    @csrf
                    <input type="hidden" name="ids" id="notification-bulk-read-ids">
                </form>
                <button type="button" id="notification-bulk-delete-btn" class="btn btn-sm btn-outline-danger d-none" data-confirm-submit data-confirm-title="Delete selected" data-confirm-message="Are you sure you want to delete the selected notifications? This action cannot be undone." data-confirm-button="Delete" data-confirm-class="btn-danger" form="notification-bulk-delete-form">
                    <i class="fa-solid fa-trash me-1"></i><span id="notification-bulk-count">0</span> selected
                </button>
                <button type="button" id="notification-bulk-read-btn" class="btn btn-sm btn-outline-primary d-none" form="notification-bulk-read-form">
                    <i class="fa-solid fa-envelope-open me-1"></i>Mark as Read
                </button>
                <span class="notification-result-count">{{ $notifications->total() }} messages</span>
            </div>
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
                <div class="notification-card {{ $notification->is_read ? '' : 'is-unread' }}" data-notification-id="{{ $notification->id }}">
                    <div class="notification-card-check">
                        <input type="checkbox" class="form-check-input notification-checkbox" value="{{ $notification->id }}" data-notification-checkbox>
                    </div>
                    <a class="notification-card-body" href="{{ $notification->link ?: '#' }}">
                        <span class="notification-icon notification-icon-{{ $meta['tone'] }}">
                            <i class="fa-solid {{ $meta['icon'] }}"></i>
                        </span>
                        <div class="notification-content">
                            <div class="notification-title-row">
                                <strong>{{ $notification->title }}</strong>
                                @unless($notification->is_read)
                                    <em data-notification-status>Unread</em>
                                @else
                                    <em data-notification-status class="text-success">Read</em>
                                @endunless
                                <time>{{ $notification->created_at->diffForHumans() }}</time>
                            </div>
                            <p>{{ $notification->message }}</p>
                        </div>
                    </a>
                </div>
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
@include('partials.confirm-modal')
@endsection

@push('styles')
<style>
/* Notification card - each row is a flex container */
.notification-card {
    display: flex;
    align-items: stretch;
    gap: 0;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: #fff;
    transition: box-shadow .18s ease, border-color .18s ease;
    overflow: hidden;
}
.notification-card:hover {
    box-shadow: 0 6px 18px rgba(26,60,110,.1);
    border-color: #BFD0E9;
}
.notification-card.is-unread {
    background: #F8FBFF;
    border-color: #BFD0E9;
}

/* Checkbox column */
.notification-card-check {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    padding: .85rem .65rem .85rem .85rem;
    border-right: 1px solid var(--border);
    background: #FAFBFE;
}
.notification-card-check .form-check-input {
    width: 1.15rem;
    height: 1.15rem;
    cursor: pointer;
    margin: 0;
}

/* Main clickable body - icon + content */
.notification-card-body {
    flex: 1;
    min-width: 0;
    display: flex;
    align-items: flex-start;
    gap: .85rem;
    padding: .95rem 1rem;
    text-decoration: none;
    color: var(--text-dark);
}
.notification-card-body:hover {
    color: var(--text-dark);
}

/* Icon */
.notification-card-body .notification-icon {
    flex: 0 0 42px;
    width: 42px;
    height: 42px;
    border-radius: 8px;
    display: grid;
    place-items: center;
    margin-top: .1rem;
}

/* Content area */
.notification-card-body .notification-content {
    flex: 1;
    min-width: 0;
}
.notification-title-row {
    display: flex;
    align-items: center;
    gap: .5rem;
    flex-wrap: wrap;
    margin-bottom: .2rem;
}
.notification-title-row strong {
    font-size: .95rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
    min-width: 0;
}
.notification-title-row time {
    color: var(--text-muted);
    font-size: .78rem;
    font-weight: 700;
    white-space: nowrap;
    margin-left: auto;
}
.notification-card-body .notification-content p {
    color: var(--text-muted);
    line-height: 1.5;
    margin: 0;
    font-size: .88rem;
}

/* Delete column */
.notification-card-delete {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    border: none;
    background: transparent;
    color: var(--danger);
    cursor: pointer;
    opacity: .35;
    transition: opacity .15s ease, background .15s ease;
    border-left: 1px solid var(--border);
}
.notification-card-delete:hover {
    opacity: 1;
    background: #FDECEC;
}
.notification-card:hover .notification-card-delete {
    opacity: .7;
}

/* Status badges - reuse existing styles */
.notification-card-body em[data-notification-status] {
    font-style: normal;
    background: #FFF7DF;
    border: 1px solid #F4D47C;
    border-radius: 999px;
    color: #7A5400;
    font-size: .72rem;
    font-weight: 800;
    padding: .15rem .5rem;
    white-space: nowrap;
}
.notification-card-body em[data-notification-status].text-success {
    background: #EAF7F1;
    border-color: #BFE5D4;
    color: #216B4F;
}

/* Mobile responsive */
@media(max-width:767px) {
    .notification-card {
        flex-wrap: nowrap;
    }
    .notification-card-body {
        padding: .75rem .65rem;
        gap: .55rem;
    }
    .notification-card-body .notification-icon {
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        font-size: .85rem;
    }
    .notification-title-row {
        gap: .4rem;
        flex-wrap: wrap;
        align-items: baseline;
    }
    .notification-title-row strong {
        font-size: .9rem;
        white-space: normal;
        overflow: visible;
        text-overflow: clip;
        flex: 1 1 auto;
        min-width: 0;
        max-width: none;
        line-height: 1.3;
    }
    .notification-title-row time {
        font-size: .72rem;
        flex: 0 0 auto;
        margin-left: 0;
        order: 3;
        width: 100%;
        margin-top: .15rem;
    }
    .notification-card-body em[data-notification-status] {
        flex: 0 0 auto;
        order: 2;
        font-size: .68rem;
        padding: .12rem .45rem;
    }
    .notification-card-body .notification-content p {
        font-size: .82rem;
        line-height: 1.45;
        margin-top: .2rem;
    }
    .notification-card-check {
        padding: .6rem .45rem;
    }
    .notification-card-check .form-check-input {
        width: 1rem;
        height: 1rem;
    }
    .notification-card-delete {
        width: 38px;
    }
    .notification-card-delete i {
        font-size: .85rem;
    }
    .notifications-panel-header {
        flex-direction: column;
        align-items: flex-start !important;
        gap: .5rem;
    }
}
@media(max-width:480px) {
    .notification-card-body {
        padding: .65rem .55rem;
        gap: .5rem;
    }
    .notification-card-body .notification-icon {
        flex: 0 0 30px;
        width: 30px;
        height: 30px;
        font-size: .78rem;
    }
    .notification-title-row strong {
        font-size: .86rem;
    }
    .notification-title-row time {
        font-size: .68rem;
    }
    .notification-card-body .notification-content p {
        font-size: .78rem;
    }
    .notification-card-check {
        padding: .5rem .35rem;
    }
    .notification-card-delete {
        width: 34px;
    }
}
</style>
@endpush
