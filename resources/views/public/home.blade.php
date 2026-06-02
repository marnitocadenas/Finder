@php
    $isStudent = auth()->check() && auth()->user()->role === 'student';
    $reportUrl = $isStudent ? route('student.lost-items.create') : route('login');
    $browseUrl = $isStudent ? route('student.browse') : '#recent-found';
@endphp

@extends('layouts.app')
@section('title','TMC Lost and Found')
@section('content')
<section class="landing-hero">
    <nav class="landing-nav container" aria-label="Public navigation">
        <a class="landing-brand" href="{{ route('home') }}">
            <img src="{{ asset('images/tmc-logo.png') }}" alt="Trinidad Municipal College logo">
            <span>
                <strong>TMC</strong>
                <small>Lost and Found</small>
            </span>
        </a>
        <div class="landing-nav-actions">
            <a href="#recent-found" class="btn btn-outline-light" data-smooth-scroll>Browse Found</a>
            <a href="{{ route('login') }}" class="btn btn-outline-light">Login</a>
            <a href="{{ route('register') }}" class="btn btn-warning">Register as Student</a>
        </div>
    </nav>

    <div class="container landing-hero-inner">
        <div class="landing-hero-copy">
            <span class="landing-kicker">Trinidad Municipal College</span>
            <h1>Lost something on campus?</h1>
            <p>Report missing belongings, search verified found items, and file ownership claims through one organized campus system.</p>
            <div class="landing-hero-actions">
                <a href="{{ $reportUrl }}" class="btn btn-warning btn-lg">
                    <i class="fa-solid fa-plus me-2"></i>Report Lost Item
                </a>
                <a href="{{ $browseUrl }}" class="btn btn-outline-light btn-lg" @unless($isStudent) data-smooth-scroll @endunless>
                    <i class="fa-solid fa-magnifying-glass me-2"></i>Browse Found
                </a>
            </div>
        </div>

        <div class="landing-hero-board" aria-label="Lost and found activity summary">
            <div class="hero-board-glow"></div>
            <div class="hero-board-header">
                <span>Campus desk activity</span>
                <strong>Live queue</strong>
            </div>
            <div class="hero-board-metric">
                <span><i class="fa-solid fa-box-open"></i></span>
                <div>
                    <strong>{{ $landingStats[0]['value'] ?? 0 }}</strong>
                    <small>{{ $landingStats[0]['label'] ?? 'Found items posted' }}</small>
                </div>
            </div>
            <div class="hero-board-flow" aria-hidden="true">
                <span class="is-active"><i class="fa-solid fa-clipboard-list"></i></span>
                <span><i class="fa-solid fa-magnifying-glass"></i></span>
                <span><i class="fa-solid fa-file-signature"></i></span>
                <span><i class="fa-solid fa-handshake"></i></span>
            </div>
            <div class="hero-board-activity">
                <div>
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>New matches checked by staff</span>
                </div>
                <div>
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Claims verified before release</span>
                </div>
                <div>
                    <i class="fa-solid fa-bell"></i>
                    <span>Students notified when items match</span>
                </div>
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
            <button class="btn btn-primary btn-lg" data-loading-text="Searching">
                <i class="fa-solid fa-search"></i>
                <span>Search</span>
            </button>
            @if(request()->filled('q') || request()->filled('category_id'))
                <a class="btn btn-outline-secondary btn-lg" href="{{ route('home') }}">
                    <i class="fa-solid fa-xmark"></i>
                    <span>Clear</span>
                </a>
            @endif
        </form>

        <div class="landing-category-chips" aria-label="Quick category filters">
            @foreach($categories->take(6) as $category)
                <a href="{{ route('home', ['category_id' => $category->id]) }}" class="@if(request('category_id') == $category->id) active @endif">
                    <i class="fa-solid {{ $category->icon ?? 'fa-tag' }}"></i>{{ $category->name }}
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="landing-stats">
    <div class="container landing-stats-grid">
        @foreach($landingStats as $stat)
            <div class="landing-stat">
                <span><i class="fa-solid {{ $stat['icon'] }}"></i></span>
                <strong>{{ $stat['value'] }}</strong>
                <small>{{ $stat['label'] }}</small>
            </div>
        @endforeach
    </div>
</section>

<section id="recent-found" class="landing-section">
    <div class="container">
        <div class="landing-section-heading">
            <div>
                <span class="landing-kicker text-primary">Recently posted</span>
                <h2>Found items waiting to be claimed</h2>
            </div>
            <a href="{{ $isStudent ? route('student.browse') : route('login') }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>{{ $isStudent ? 'Open Student Browse' : 'Login to Claim' }}
            </a>
        </div>

        <div class="row g-3">
            @forelse($foundItems as $item)
                <div class="col-sm-6 col-lg-3">
                    <article class="found-preview-card">
                        <a class="found-preview-media" href="{{ route('public.found-items.show', $item) }}">
                            @if($item->image)
                                <img src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->title }}">
                            @else
                                <div><i class="fa-solid {{ $item->category->icon ?? 'fa-box' }}"></i></div>
                            @endif
                            <x-status :status="$item->status" />
                        </a>
                        <div class="found-preview-body">
                            <span class="found-preview-category">
                                <i class="fa-solid {{ $item->category->icon ?? 'fa-tag' }}"></i>{{ $item->category->name ?? 'General' }}
                            </span>
                            <h3>{{ $item->title }}</h3>
                            <p><i class="fa-solid fa-location-dot"></i>{{ $item->location_found }}</p>
                            <p><i class="fa-solid fa-calendar-day"></i>{{ optional($item->date_found)->format('M d, Y') ?? 'Date pending' }}</p>
                        </div>
                        <div class="found-preview-actions">
                            <a href="{{ route('public.found-items.show', $item) }}" class="btn btn-outline-primary">
                                <i class="fa-solid fa-eye me-1"></i>View Details
                            </a>
                            <a href="{{ $isStudent ? route('student.claims.create', ['found_item_id' => $item->id]) : route('login') }}" class="btn btn-primary @if($item->status !== 'unclaimed') disabled @endif">
                                <i class="fa-solid fa-file-signature me-1"></i>{{ $isStudent ? 'File Claim' : 'Login to Claim' }}
                            </a>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="landing-empty">
                        <i class="fa-solid fa-box-open"></i>
                        <h3>No matching found items</h3>
                        <p>No found items match your search. Try another keyword or report your lost item.</p>
                        <a href="{{ $reportUrl }}" class="btn btn-warning">
                            <i class="fa-solid fa-plus me-1"></i>Report Lost Item
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

<section class="landing-process">
    <div class="container">
        <div class="landing-section-heading">
            <div>
                <span class="landing-kicker text-primary">How it works</span>
                <h2>From report to release</h2>
            </div>
        </div>
        <div class="row g-3">
            @foreach([
                ['icon' => 'fa-clipboard-list', 'title' => 'Report your lost item', 'copy' => 'Submit item details, date, location, and an optional photo so staff can compare possible matches.'],
                ['icon' => 'fa-wand-magic-sparkles', 'title' => 'Staff compares matches', 'copy' => 'The system helps surface possible matches between student reports and verified found items.'],
                ['icon' => 'fa-file-signature', 'title' => 'Submit claim proof', 'copy' => 'When you recognize an item, file a claim with ownership details and proof for staff review.'],
                ['icon' => 'fa-handshake', 'title' => 'Claim after approval', 'copy' => 'Approved claims receive pickup instructions before the item is released by authorized staff.'],
            ] as $index => $step)
                <div class="col-md-6 col-xl-3">
                    <div class="process-step">
                        <i class="fa-solid {{ $step['icon'] }}"></i>
                        <span>{{ $index + 1 }}</span>
                        <h3>{{ $step['title'] }}</h3>
                        <p>{{ $step['copy'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="landing-final-cta">
    <div class="container">
        <div>
            <span class="landing-kicker">Start now</span>
            <h2>Missing something? Start a report now.</h2>
            <p>Students can report lost belongings, save found items, and track claim updates from their dashboard.</p>
        </div>
        <div class="landing-final-actions">
            <a href="{{ $reportUrl }}" class="btn btn-warning btn-lg">
                <i class="fa-solid fa-plus me-1"></i>Report Lost Item
            </a>
            <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">
                <i class="fa-solid fa-user-plus me-1"></i>Register as Student
            </a>
        </div>
    </div>
</section>

<footer class="landing-footer">
    <div class="container">
        <div>
            <strong>Trinidad Municipal College Lost and Found Office</strong>
            <span><i class="fa-solid fa-location-dot"></i> Campus Student Services Area</span>
            <span><i class="fa-solid fa-clock"></i> Monday to Friday, 8:00 AM - 5:00 PM</span>
        </div>
        <a href="{{ route('login') }}" class="btn btn-warning">Get Started</a>
    </div>
</footer>
@endsection
