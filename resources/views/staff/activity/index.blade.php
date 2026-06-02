@extends('layouts.app')
@section('title','My Activity')
@section('content')
<div class="staff-activity-module">
    <div class="staff-activity-hero">
        <div>
            <span class="module-eyebrow">Staff history</span>
            <h1>My Activity</h1>
            <p>Track your found item posts, claim reviews, release actions, and recent system work.</p>
        </div>
    </div>

    <div class="staff-performance-grid">
        <div class="staff-performance-card staff-performance-primary"><span><i class="fa-solid fa-box-open"></i></span><div><small>Items Posted</small><strong>{{ $postedItems }}</strong></div></div>
        <div class="staff-performance-card staff-performance-success"><span><i class="fa-solid fa-clipboard-check"></i></span><div><small>Claims Reviewed</small><strong>{{ $reviewedClaims }}</strong></div></div>
        <div class="staff-performance-card staff-performance-warning"><span><i class="fa-solid fa-handshake"></i></span><div><small>Items Released</small><strong>{{ $releasedItems }}</strong></div></div>
    </div>

    <section class="logs-panel">
        <div class="logs-panel-header">
            <div>
                <span class="module-eyebrow">Timeline</span>
                <h2>Recent Staff Actions</h2>
            </div>
        </div>
        @include('partials.filters', ['statuses' => []])
        <div class="activity-feed">
            @forelse($logs as $log)
                <div class="activity-item">
                    <div class="activity-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                    <div>
                        <strong>{{ $log->target_type ?: 'System' }}</strong>
                        <p>{{ $log->action }}</p>
                    </div>
                    <time>{{ $log->created_at->diffForHumans() }}</time>
                </div>
            @empty
                <div class="empty-state"><i class="fa-solid fa-clock"></i><p>No staff activity yet.</p></div>
            @endforelse
        </div>
        <div class="mt-3">{{ $logs->links() }}</div>
    </section>
</div>
@endsection
