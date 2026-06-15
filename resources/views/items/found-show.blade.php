@extends('layouts.app')
@section('title','Found Item Details')
@section('content')
<div class="found-detail-module">
    <div class="found-hero">
        <div>
            <span class="module-eyebrow">Found item</span>
            <h1>{{ $item->title }}</h1>
            <p>{{ $item->location_found }} &bull; {{ optional($item->date_found)->format('M d, Y') }}</p>
        </div>
        <a href="{{ ($role ?? 'admin') === 'student' ? route('student.found-items.index') : (($role ?? 'admin') === 'staff' ? route('staff.found-items.index') : route('admin.found-items.index')) }}" class="btn btn-light">
            <i class="fa-solid fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="found-detail-grid">
        <section class="found-detail-card">
            <div class="found-detail-heading">
                <div>
                    <span class="module-eyebrow">Details</span>
                    <h2>{{ $item->title }}</h2>
                </div>
                <x-status :status="$item->status" />
            </div>
            <p class="found-description">{{ $item->description }}</p>
            <dl class="found-detail-list">
                <div><dt>Reporter</dt><dd>{{ $item->staff->name ?? ($item->guest_name ? $item->guest_name.' (Public report)' : 'Unknown') }}</dd></div>
                @if($item->guest_contact)<div><dt>Contact</dt><dd>{{ $item->guest_contact }}</dd></div>@endif
                <div><dt>Category</dt><dd>{{ $item->category->name }}</dd></div>
                <div><dt>Date Found</dt><dd>{{ optional($item->date_found)->format('M d, Y') }}</dd></div>
                <div><dt>Location</dt><dd>{{ $item->location_found }}</dd></div>
            </dl>
        </section>

        <aside class="found-photo-card">
            @if($item->image)
                <button type="button" class="image-preview-button" data-image-preview="{{ asset('storage/'.$item->image) }}">
                    <img src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->title }}">
                </button>
            @else
                <div class="found-photo-empty">
                    <i class="fa-solid fa-image"></i>
                    <p>No image uploaded.</p>
                </div>
            @endif
        </aside>
    </div>
</div>
@include('partials.image-preview-modal')
@endsection
