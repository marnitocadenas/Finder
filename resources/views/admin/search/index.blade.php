@extends('layouts.app')
@section('title','Global Search')
@section('content')
<div class="admin-search-module">
    <div class="admin-search-hero">
        <div>
            <span class="module-eyebrow">Admin finder</span>
            <h1>Global Search</h1>
            <p>Find users, lost reports, found items, and claims from one workspace.</p>
        </div>
    </div>

    <form class="admin-search-panel">
        <i class="fa-solid fa-search"></i>
        <input class="form-control" name="q" value="{{ $term }}" placeholder="Search by name, email, item, location, or claim details" autofocus>
        <button class="btn btn-primary">Search</button>
    </form>

    <div class="admin-search-grid">
        <section class="module-card">
            <div class="module-card-header"><div><span class="module-eyebrow">Accounts</span><h2>Users</h2></div><span class="overview-rate">{{ $users->count() }}</span></div>
            <div class="search-result-list">
                @forelse($users as $user)
                    <a href="{{ route('admin.users.edit', $user) }}"><i class="fa-solid fa-user"></i><span><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></span></a>
                @empty
                    <div class="empty-state compact"><p>No users found.</p></div>
                @endforelse
            </div>
        </section>

        <section class="module-card">
            <div class="module-card-header"><div><span class="module-eyebrow">Reports</span><h2>Lost Items</h2></div><span class="overview-rate">{{ $lostItems->count() }}</span></div>
            <div class="search-result-list">
                @forelse($lostItems as $item)
                    <a href="{{ route('admin.lost-items.edit', $item) }}"><i class="fa-solid {{ $item->category->icon ?? 'fa-box' }}"></i><span><strong>{{ $item->title }}</strong><small>{{ $item->location_lost }}</small></span></a>
                @empty
                    <div class="empty-state compact"><p>No lost reports found.</p></div>
                @endforelse
            </div>
        </section>

        <section class="module-card">
            <div class="module-card-header"><div><span class="module-eyebrow">Inventory</span><h2>Found Items</h2></div><span class="overview-rate">{{ $foundItems->count() }}</span></div>
            <div class="search-result-list">
                @forelse($foundItems as $item)
                    <a href="{{ route('admin.found-items.edit', $item) }}"><i class="fa-solid {{ $item->category->icon ?? 'fa-box-open' }}"></i><span><strong>{{ $item->title }}</strong><small>{{ $item->location_found }}</small></span></a>
                @empty
                    <div class="empty-state compact"><p>No found items found.</p></div>
                @endforelse
            </div>
        </section>

        <section class="module-card">
            <div class="module-card-header"><div><span class="module-eyebrow">Requests</span><h2>Claims</h2></div><span class="overview-rate">{{ $claims->count() }}</span></div>
            <div class="search-result-list">
                @forelse($claims as $claim)
                    <a href="{{ route('admin.claims.show', $claim) }}"><i class="fa-solid fa-file-signature"></i><span><strong>Claim #{{ $claim->id }} - {{ $claim->foundItem->title ?? 'Item' }}</strong><small>{{ $claim->student->name ?? 'Student' }}</small></span></a>
                @empty
                    <div class="empty-state compact"><p>No claims found.</p></div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
