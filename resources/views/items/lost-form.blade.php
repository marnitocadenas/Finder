@extends('layouts.app')
@section('title', $item->exists ? 'Edit Lost Report' : 'Report Lost Item')
@section('content')
<div class="lost-form-module">
    <div class="lost-hero">
        <div>
            <span class="module-eyebrow">Lost report</span>
            <h1>{{ $item->exists ? 'Edit Lost Report' : 'Report Lost Item' }}</h1>
            <p>{{ $item->exists ? 'Update the item details, status, and supporting photo.' : 'Submit clear details so staff can match your report with found items.' }}</p>
        </div>
        <a href="{{ $role === 'student' ? route('student.lost-items.index') : route('admin.lost-items.index') }}" class="btn btn-light">
            <i class="fa-solid fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="lost-form-card">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="lost-form-section">
            <div>
                <h2>Item Details</h2>
                <p>Use a specific title, category, and description to improve matching.</p>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="lost-title">Title</label>
                    <input id="lost-title" class="form-control" name="title" value="{{ old('title', $item->title) }}" placeholder="Item name" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="lost-category">Category</label>
                    <select id="lost-category" class="form-select" name="category_id" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $item->category_id)==$category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label" for="lost-description">Description</label>
                    <textarea id="lost-description" class="form-control" name="description" rows="5" required>{{ old('description', $item->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="lost-form-section">
            <div>
                <h2>Loss Information</h2>
                <p>Dates and locations help staff compare the report against found item logs.</p>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="lost-date">Date Lost</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-calendar-day"></i></span>
                        <input id="lost-date" class="form-control" type="date" name="date_lost" value="{{ old('date_lost', optional($item->date_lost)->format('Y-m-d')) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="lost-location">Location Lost</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-location-dot"></i></span>
                        <input id="lost-location" class="form-control" name="location_lost" value="{{ old('location_lost', $item->location_lost) }}" placeholder="Building, room, or area" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="lost-status">Status</label>
                    <select id="lost-status" class="form-select" name="status">
                        <option value="lost" @selected(old('status', $item->status)==='lost')>Lost</option>
                        <option value="found" @selected(old('status', $item->status)==='found')>Found</option>
                        <option value="closed" @selected(old('status', $item->status)==='closed')>Closed</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="lost-image">Image</label>
                    @if($item->image)
                        <div class="lost-current-image">
                            <img class="lazy-thumb skeleton" loading="lazy" decoding="async" src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->title }}" onload="this.classList.remove('skeleton')">
                            <span>Current image</span>
                        </div>
                    @endif
                    <input id="lost-image" class="form-control" type="file" name="image" accept="image/*">
                </div>
            </div>
        </div>

        <div class="lost-form-actions">
            <button class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk me-1"></i>Save Report
            </button>
            <a href="{{ $role === 'student' ? route('student.lost-items.index') : route('admin.lost-items.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
