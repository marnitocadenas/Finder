@php
    $isStudent = auth()->check() && auth()->user()->role === 'student';
@endphp

@extends('layouts.app')
@section('title', $item->title)
@section('content')
<section class="public-detail-hero">
    <nav class="landing-nav container" aria-label="Public navigation">
        <a class="landing-brand" href="{{ route('home') }}">
            <img src="{{ asset('images/tmc-logo.png') }}" alt="Trinidad Municipal College logo">
            <span>
                <strong>TMC</strong>
                <small>Lost and Found</small>
            </span>
        </a>
        <div class="landing-nav-actions">
            <a href="{{ route('home', ['category_id' => $item->category_id]) }}" class="btn btn-outline-light">Similar Items</a>
            <a href="{{ route('login') }}" class="btn btn-warning">Login</a>
        </div>
    </nav>

    <div class="container public-detail-heading">
        <a href="{{ route('home') }}#recent-found" class="public-back-link">
            <i class="fa-solid fa-arrow-left"></i>Back to found items
        </a>
        <span class="landing-kicker">{{ $item->category->name ?? 'Found item' }}</span>
        <h1>{{ $item->title }}</h1>
        <p>{{ $item->location_found }} &bull; {{ optional($item->date_found)->format('M d, Y') }}</p>
    </div>
</section>

<section class="public-detail-section">
    <div class="container public-detail-grid">
        <aside class="public-detail-media">
            @if($item->image)
                <button type="button" class="image-preview-button" data-image-preview="{{ asset('storage/'.$item->image) }}">
                    <img src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->title }}">
                </button>
            @else
                <div class="public-detail-empty">
                    <i class="fa-solid {{ $item->category->icon ?? 'fa-box-open' }}"></i>
                    <p>No image uploaded for this found item.</p>
                </div>
            @endif
        </aside>

        <article class="public-detail-card">
            <div class="public-detail-card-header">
                <div>
                    <span class="landing-kicker text-primary">Item details</span>
                    <h2>{{ $item->title }}</h2>
                </div>
                <x-status :status="$item->status" />
            </div>

            <p class="public-detail-description">{{ $item->description }}</p>

            <dl class="public-detail-list">
                <div><dt>Category</dt><dd>{{ $item->category->name ?? 'General' }}</dd></div>
                <div><dt>Date Found</dt><dd>{{ optional($item->date_found)->format('M d, Y') ?? 'Date pending' }}</dd></div>
                <div><dt>Location</dt><dd>{{ $item->location_found }}</dd></div>
                <div><dt>Posted By</dt><dd>{{ $item->staff->name ?? 'Authorized staff' }}</dd></div>
            </dl>

            <div class="public-detail-actions">
                @if($item->status === 'unclaimed')
                    <a href="{{ $isStudent ? route('student.claims.create', ['found_item_id' => $item->id]) : route('login') }}" class="btn btn-primary">
                        <i class="fa-solid fa-file-signature me-1"></i>{{ $isStudent ? 'File Claim' : 'Login to Claim' }}
                    </a>
                @else
                    <span class="btn btn-light disabled"><i class="fa-solid fa-lock me-1"></i>Claim unavailable</span>
                @endif
                <a href="{{ $isStudent ? route('student.lost-items.create') : route('login') }}" class="btn btn-outline-primary">
                    <i class="fa-solid fa-plus me-1"></i>Report Lost Item
                </a>
            </div>
        </article>
    </div>
</section>
@include('partials.image-preview-modal')
@endsection
