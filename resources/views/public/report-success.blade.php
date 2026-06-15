@extends('layouts.app')
@section('title', 'Report Submitted')
@section('content')
<section class="landing-hero" style="min-height:60vh">
    <nav class="landing-nav container" aria-label="Public navigation">
        <a class="landing-brand" href="{{ route('home') }}">
            <img src="{{ asset('images/tmc-logo.png') }}" alt="Trinidad Municipal College logo">
            <span><strong>TMC</strong><small>Lost and Found</small></span>
        </a>
        <div class="landing-nav-actions">
            <a href="{{ route('home') }}" class="btn btn-outline-light"><i class="fa-solid fa-house me-2 nav-btn-icon"></i>Home</a>
        </div>
    </nav>
    <div class="container landing-hero-inner" style="padding-top:2rem">
        <div class="landing-hero-copy" style="text-align:center;max-width:600px;margin:0 auto">
            <div style="font-size:4rem;margin-bottom:1rem"><i class="fa-solid fa-circle-check" style="color:#198754"></i></div>
            <span class="landing-kicker">Thank you</span>
            <h1>{{ $type === 'lost' ? 'Lost Report Submitted' : 'Found Item Reported' }}</h1>
            <p>
                @if($type === 'lost')
                    Your lost item report has been received. Campus staff will review it and contact you at <strong>{{ $contact }}</strong> if a matching item is found.
                @else
                    Your found item report has been received. Campus staff will review it and list it for the owner. They may contact you at <strong>{{ $contact }}</strong> for follow-up.
                @endif
            </p>
            <div class="landing-hero-actions" style="justify-content:center;margin-top:2rem">
                @if($type === 'lost')
                    <a href="{{ route('public.report-found.create') }}" class="btn btn-outline-light btn-lg">
                        <i class="fa-solid fa-box-open me-2"></i>Report Found Item
                    </a>
                @else
                    <a href="{{ route('public.report-lost.create') }}" class="btn btn-outline-light btn-lg">
                        <i class="fa-solid fa-clipboard-list me-2"></i>Report Lost Item
                    </a>
                @endif
                <a href="{{ route('home') }}" class="btn btn-warning btn-lg">
                    <i class="fa-solid fa-house me-2"></i>Back to Home
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
