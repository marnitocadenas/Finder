@extends('layouts.app')
@section('title','Staff Overview')
@section('content')
<div class="staff-overview">
    <div class="staff-hero">
        <div>
            <span class="module-eyebrow">Staff workspace</span>
            <h1>Overview</h1>
            <p>Post found items, review claim requests, and compare student lost reports from one focused workspace.</p>
        </div>
        <div class="staff-hero-actions">
            <a class="btn btn-warning" href="{{ route('staff.found-items.create') }}">
                <i class="fa-solid fa-plus me-1"></i>Post Found Item
            </a>
            <a class="btn btn-light" href="{{ route('staff.claims.index') }}">
                <i class="fa-solid fa-inbox me-1"></i>Claims Inbox
            </a>
        </div>
    </div>

    @include('partials.stats')

    <div class="row g-3 align-items-stretch">
        <div class="col-xl-8">
            <section class="staff-panel">
                <div class="staff-panel-header">
                    <div>
                        <span class="module-eyebrow">Needs attention</span>
                        <h2>Today&apos;s Work Queue</h2>
                    </div>
                </div>
                <div class="staff-action-grid">
                    @foreach($workQueue as $item)
                        <a class="staff-action staff-action-{{ $item['tone'] }}" href="{{ $item['route'] }}">
                            <span><i class="fa-solid {{ $item['icon'] }}"></i></span>
                            <strong>{{ $item['value'] }}</strong>
                            <small>{{ $item['label'] }}</small>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="col-xl-4">
            <section class="staff-panel staff-panel-accent">
                <div class="staff-panel-header">
                    <div>
                        <span class="module-eyebrow">Shortcuts</span>
                        <h2>Quick Actions</h2>
                    </div>
                </div>
                <div class="staff-quick-links">
                    <a href="{{ route('staff.found-items.create') }}"><i class="fa-solid fa-plus"></i><span>Post Found</span></a>
                    <a href="{{ route('staff.found-items.index') }}"><i class="fa-solid fa-box-open"></i><span>My Found Items</span></a>
                    <a href="{{ route('staff.claims.index') }}"><i class="fa-solid fa-inbox"></i><span>Claims Inbox</span></a>
                    <a href="{{ route('staff.lost-reports.index') }}"><i class="fa-solid fa-magnifying-glass"></i><span>Lost Reports</span></a>
                </div>
            </section>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-7">
            <section class="staff-panel">
                <div class="staff-panel-header">
                    <div>
                        <span class="module-eyebrow">Inventory</span>
                        <h2>Recent Found Items</h2>
                    </div>
                    <a href="{{ route('staff.found-items.index') }}" class="btn btn-light btn-sm">View All</a>
                </div>
                <div class="staff-list">
                    @forelse($recentFoundItems as $item)
                        <a class="staff-list-item" href="{{ route('staff.found-items.show', $item) }}">
                            @if($item->image)
                                <img class="lazy-thumb skeleton" loading="lazy" decoding="async" src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->title }}" onload="this.classList.remove('skeleton')">
                            @else
                                <span><i class="fa-solid fa-box-open"></i></span>
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
                            <i class="fa-solid fa-box-open"></i>
                            <p>No found items posted yet.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="col-xl-5">
            <section class="staff-panel">
                <div class="staff-panel-header">
                    <div>
                        <span class="module-eyebrow">Review queue</span>
                        <h2>Recent Claims</h2>
                    </div>
                    <a href="{{ route('staff.claims.index') }}" class="btn btn-light btn-sm">Open Inbox</a>
                </div>
                <div class="staff-claim-list">
                    @forelse($recentClaims as $claim)
                        <a class="staff-claim-item" href="{{ route('staff.claims.show', $claim) }}">
                            <span>{{ strtoupper(substr($claim->student->name ?? 'S', 0, 1)) }}</span>
                            <div>
                                <strong>{{ $claim->student->name ?? 'Student' }}</strong>
                                <small>{{ $claim->foundItem->title ?? 'Found item' }} &bull; {{ $claim->created_at->diffForHumans() }}</small>
                            </div>
                            <x-status :status="$claim->status" />
                        </a>
                    @empty
                        <div class="empty-state">
                            <i class="fa-solid fa-file-signature"></i>
                            <p>No claims for your posted items yet.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
