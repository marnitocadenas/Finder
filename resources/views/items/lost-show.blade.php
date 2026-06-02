@extends('layouts.app')
@section('title','Lost Report Details')
@section('content')
<div class="lost-detail-module">
    <div class="lost-hero">
        <div>
            <span class="module-eyebrow">Lost report</span>
            <h1>{{ $item->title }}</h1>
            <p>{{ $item->location_lost }} &bull; {{ optional($item->date_lost)->format('M d, Y') }}</p>
        </div>
        <a href="{{ ($role ?? 'admin') === 'staff' ? route('staff.lost-reports.index') : (($role ?? 'admin') === 'student' ? route('student.lost-items.index') : route('admin.lost-items.index')) }}" class="btn btn-light">
            <i class="fa-solid fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="lost-detail-grid">
        <section class="lost-detail-card">
            <div class="lost-detail-heading">
                <div>
                    <span class="module-eyebrow">Details</span>
                    <h2>{{ $item->title }}</h2>
                </div>
                <x-status :status="$item->status" />
            </div>
            <p class="lost-description">{{ $item->description }}</p>
            <dl class="lost-detail-list">
                <div><dt>Student</dt><dd>{{ $item->user->name }}</dd></div>
                <div><dt>Category</dt><dd>{{ $item->category->name }}</dd></div>
                <div><dt>Date Lost</dt><dd>{{ optional($item->date_lost)->format('M d, Y') }}</dd></div>
                <div><dt>Location</dt><dd>{{ $item->location_lost }}</dd></div>
            </dl>
        </section>

        <aside class="lost-photo-card">
            @if($item->image)
                <button type="button" class="image-preview-button" data-image-preview="{{ asset('storage/'.$item->image) }}">
                    <img src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->title }}">
                </button>
            @else
                <div class="lost-photo-empty">
                    <i class="fa-solid fa-image"></i>
                    <p>No image uploaded.</p>
                </div>
            @endif
        </aside>
    </div>
</div>
@include('partials.image-preview-modal')
@endsection
