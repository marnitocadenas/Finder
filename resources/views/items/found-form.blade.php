@extends('layouts.app')
@section('title', $item->exists ? 'Edit Found Item' : ($role === 'student' ? 'Report Found Item' : 'Post Found Item'))
@section('content')
<div class="found-form-module">
    <div class="found-hero">
        <div>
            <span class="module-eyebrow">Found item</span>
            <h1>{{ $item->exists ? 'Edit Found Item' : ($role === 'student' ? 'Report Found Item' : 'Post Found Item') }}</h1>
            <p>{{ $item->exists ? 'Update item details, claim status, and supporting photo.' : ($role === 'student' ? 'Found something on campus? Report it here so the owner can find it. Please bring the item to the campus front desk after submitting.' : 'Record a recovered item so students can identify and claim it.') }}</p>
        </div>
        <a href="{{ $role === 'student' ? route('student.found-items.index') : ($role === 'staff' ? route('staff.found-items.index') : route('admin.found-items.index')) }}" class="btn btn-light">
            <i class="fa-solid fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="found-form-card {{ in_array($role, ['staff', 'student']) ? 'staff-intake-form' : '' }}">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        @if($role === 'admin')
        <div class="found-form-section">
            <div>
                <h2>Reported By</h2>
                <p>Assign this found item to a staff account.</p>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="staff_id">Staff</label>
                    <select id="staff_id" class="form-select" name="staff_id">
                        <option value="">-- No staff assigned --</option>
                        @foreach($staffUsers ?? [] as $staff)
                            <option value="{{ $staff->id }}" @selected(old('staff_id', $item->staff_id)==$staff->id)>{{ $staff->name }} ({{ $staff->email }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        @endif

        <div class="found-form-section">
            <div>
                @if(in_array($role, ['staff', 'student']))<span class="intake-step">Step 1</span>@endif
                <h2>Item Details</h2>
                <p>Use clear naming and descriptions so students can recognize their belongings.</p>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="found-title">Title</label>
                    <input id="found-title" class="form-control" name="title" value="{{ old('title', $item->title) }}" placeholder="Item name" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="found-category">Category</label>
                    <select id="found-category" class="form-select" name="category_id" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $item->category_id)==$category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label" for="found-description">Description</label>
                    <textarea id="found-description" class="form-control" name="description" rows="5" required>{{ old('description', $item->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="found-form-section">
            <div>
                @if(in_array($role, ['staff', 'student']))<span class="intake-step">Step 2</span>@endif
                <h2>Found Information</h2>
                <p>Dates, locations, and status help staff process claims accurately.</p>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="found-date">Date Found</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-calendar-day"></i></span>
                        <input id="found-date" class="form-control" type="date" name="date_found" value="{{ old('date_found', optional($item->date_found)->format('Y-m-d')) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="found-location">Location Found</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-location-dot"></i></span>
                        <input id="found-location" class="form-control" name="location_found" value="{{ old('location_found', $item->location_found) }}" placeholder="Building, room, or area" required>
                    </div>
                </div>
                @if($role !== 'student')
                <div class="col-md-6">
                    <label class="form-label" for="found-status">Status</label>
                    <select id="found-status" class="form-select" name="status">
                        <option value="unclaimed" @selected(old('status', $item->status)==='unclaimed')>Unclaimed</option>
                        <option value="claimed" @selected(old('status', $item->status)==='claimed')>Claimed</option>
                        <option value="turned_over" @selected(old('status', $item->status)==='turned_over')>Turned Over</option>
                    </select>
                </div>
                @endif
                <div class="col-md-6">
                    <label class="form-label" for="found-image">Image</label>
                    @if($item->image)
                        <div class="found-current-image">
                            <img src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->title }}">
                            <span>Current image</span>
                        </div>
                    @endif
                    <input id="found-image" class="form-control" type="file" name="image" accept="image/*">
                </div>
            </div>
        </div>

        <div class="found-form-actions found-form-actions-{{ $role }}">
            @if($role === 'staff')
                <a href="{{ route('staff.matches') }}" class="btn btn-outline-primary">
                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Review Matches
                </a>
            @endif
            <button class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk me-1"></i>Save Item
            </button>
            <a href="{{ $role === 'student' ? route('student.found-items.index') : ($role === 'staff' ? route('staff.found-items.index') : route('admin.found-items.index')) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
