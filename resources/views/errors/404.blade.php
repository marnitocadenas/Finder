@extends('layouts.app')
@section('title', 'Not Found')
@section('content')
<div class="d-flex flex-column align-items-center justify-content-center text-center" style="min-height:60vh;padding:2rem;">
    <div style="width:80px;height:80px;border-radius:16px;background:#FFF7DF;display:grid;place-items:center;margin-bottom:1.5rem;">
        <i class="fa-solid fa-inbox" style="font-size:2.2rem;color:#9A6700;"></i>
    </div>
    <h1 style="font-family:'Playfair Display',serif;font-size:2rem;margin-bottom:.5rem;">Page Not Found</h1>
    <p style="color:var(--text-muted);max-width:420px;margin-bottom:1.5rem;">
        The item or notification you tried to access has been deleted and is no longer available.
    </p>
    <div class="d-flex gap-2 flex-wrap justify-content-center">
        <a href="javascript:history.back()" class="btn btn-outline-primary">
            <i class="fa-solid fa-arrow-left me-1"></i>Go Back
        </a>
        @if(auth()->check())
        <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="btn btn-primary">
            <i class="fa-solid fa-gauge me-1"></i>Dashboard
        </a>
        @if(auth()->user()->role !== 'public')
        <a href="{{ route('notifications') }}" class="btn btn-light">
            <i class="fa-solid fa-bell me-1"></i>Notifications
        </a>
        @endif
        @else
        <a href="{{ route('home') }}" class="btn btn-primary">
            <i class="fa-solid fa-house me-1"></i>Home
        </a>
        @endif
    </div>
</div>
@endsection
