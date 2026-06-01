@extends('layouts.app')
@section('title','TMC Lost and Found')
@section('content')
<section class="landing-hero">
    <nav class="landing-nav container" aria-label="Public navigation">
        <a class="landing-brand" href="{{ route('home') }}">
            <img loading="lazy" decoding="async" src="{{ asset('images/tmc-logo.png') }}" alt="Trinidad Municipal College logo">
            <span>
                <strong>TMC</strong>
                <small>Lost and Found</small>
            </span>
        </a>
        <div class="landing-nav-actions">
            <a href="{{ route('login') }}" class="btn btn-outline-light">Login</a>
            <a href="{{ route('register') }}" class="btn btn-warning">Register</a>
        </div>
    </nav>

    <div class="container landing-hero-inner">
        <div class="landing-hero-copy">
            <span class="landing-kicker">Trinidad Municipal College</span>
            <h1>Lost something on campus?</h1>
            <p>Report missing belongings, browse verified found items, and track claims through one organized campus system.</p>
            <div class="landing-hero-actions">
                <a href="{{ route('login') }}" class="btn btn-warning btn-lg">
                    <i class="fa-solid fa-plus me-2"></i>Report Lost
                </a>
                <a href="#recent-found" class="btn btn-outline-light btn-lg">
                    <i class="fa-solid fa-magnifying-glass me-2"></i>Browse Found
                </a>
            </div>
        </div>

        <div class="landing-quick-card" aria-label="Campus lost and found summary">
            <div class="quick-card-header">
                <span>Recent activity</span>
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="quick-card-row">
                <strong>{{ $foundItems->count() }}</strong>
                <span>found items visible now</span>
            </div>
            <div class="quick-card-row">
                <strong>{{ $categories->count() }}</strong>
                <span>organized item categories</span>
            </div>
            <div class="quick-card-note">
                Claims are reviewed by authorized campus staff before release.
            </div>
        </div>
    </div>
</section>

<section class="landing-search-wrap">
    <div class="container">
        <form class="landing-search" action="{{ route('home') }}" method="GET">
            <div>
                <label for="landing-q">Search found items</label>
                <input id="landing-q" name="q" class="form-control form-control-lg" value="{{ request('q') }}" placeholder="Bag, ID, keys, umbrella">
            </div>
            <div>
                <label for="landing-category">Category</label>
                <select id="landing-category" name="category_id" class="form-select form-select-lg">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id')==$category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary btn-lg">
                <i class="fa-solid fa-search"></i>
                <span>Search</span>
            </button>
        </form>
    </div>
</section>

<section id="recent-found" class="landing-section">
    <div class="container">
        <div class="landing-section-heading">
            <div>
                <span class="landing-kicker text-primary">Recently posted</span>
                <h2>Found items waiting to be claimed</h2>
            </div>
            <a href="{{ route('login') }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Login to claim
            </a>
        </div>

        <div class="row g-3">
            @forelse($foundItems as $item)
                <div class="col-sm-6 col-lg-3">
                    <article class="found-preview-card">
                        <div class="found-preview-icon">
                            <i class="fa-solid {{ $item->category->icon ?? 'fa-box' }}"></i>
                        </div>
                        <div class="found-preview-body">
                            <h3>{{ $item->title }}</h3>
                            <p>
                                <i class="fa-solid fa-location-dot"></i>
                                {{ $item->location_found }}
                            </p>
                            <p>
                                <i class="fa-solid fa-calendar-day"></i>
                                {{ optional($item->date_found)->format('M d, Y') ?? 'Date pending' }}
                            </p>
                        </div>
                        <div class="found-preview-footer">
                            <span>{{ $item->category->name ?? 'General' }}</span>
                            <x-status :status="$item->status" />
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="landing-empty">
                        <i class="fa-solid fa-box-open"></i>
                        <p>No found items posted yet.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

<section class="landing-process">
    <div class="container">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="process-step">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <h3>Report</h3>
                    <p>Submit the item details, location, date, and photo so staff can compare possible matches.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="process-step">
                    <i class="fa-solid fa-magnifying-glass-location"></i>
                    <h3>Match</h3>
                    <p>Browse recent found items by keyword or category and check the posted campus location.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="process-step">
                    <i class="fa-solid fa-handshake"></i>
                    <h3>Claim</h3>
                    <p>File a claim with proof of ownership and wait for staff review before item release.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="landing-footer">
    <div class="container">
        <div>
            <strong>Trinidad Municipal College Lost and Found Office</strong>
            <span>Secure campus item reporting and claims management.</span>
        </div>
        <a href="{{ route('login') }}" class="btn btn-warning">Get Started</a>
    </div>
</footer>
@endsection
