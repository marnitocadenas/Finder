@extends('layouts.app')
@section('title', 'Report Found Item')
@section('content')
<section class="landing-hero" style="min-height:auto;padding-bottom:2rem">
    <nav class="landing-nav container" aria-label="Public navigation">
        <a class="landing-brand" href="{{ route('home') }}">
            <img src="{{ asset('images/tmc-logo.png') }}" alt="Trinidad Municipal College logo">
            <span><strong>TMC</strong><small>Lost and Found</small></span>
        </a>
        <div class="landing-nav-actions">
            <a href="{{ route('home') }}" class="btn btn-outline-light"><i class="fa-solid fa-arrow-left me-2 nav-btn-icon"></i>Back to Home</a>
        </div>
    </nav>
    <div class="container landing-hero-inner" style="padding-top:1rem">
        <div class="landing-hero-copy" style="text-align:left;max-width:100%">
            <span class="landing-kicker">Public Report</span>
            <h1>Report a Found Item</h1>
            <p>No account needed. Submit details about an item you found on campus and staff will list it for the owner to claim.</p>
        </div>
    </div>
</section>

<section style="padding:0 0 3rem">
    <div class="container">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('public.report-found.store') }}" enctype="multipart/form-data">
                    @csrf

                    <h5 class="mb-3 text-primary"><i class="fa-solid fa-user me-2"></i>Your Information</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" for="guest_name">Your Name <span class="text-danger">*</span></label>
                            <input id="guest_name" class="form-control" name="guest_name" value="{{ old('guest_name') }}" placeholder="Full name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="guest_contact">Contact (Email or Phone) <span class="text-danger">*</span></label>
                            <input id="guest_contact" class="form-control" name="guest_contact" value="{{ old('guest_contact') }}" placeholder="email@example.com or phone number" required>
                        </div>
                    </div>

                    <hr class="mb-4">

                    <h5 class="mb-3 text-primary"><i class="fa-solid fa-box-open me-2"></i>Item Details</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" for="title">Item Title <span class="text-danger">*</span></label>
                            <input id="title" class="form-control" name="title" value="{{ old('title') }}" placeholder="e.g. Blue backpack, Student ID" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="category_id">Category <span class="text-danger">*</span></label>
                            <select id="category_id" class="form-select" name="category_id" required>
                                <option value="">Select category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id')==$category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="description">Description <span class="text-danger">*</span></label>
                            <textarea id="description" class="form-control" name="description" rows="4" placeholder="Describe the item in detail (color, brand, distinguishing features)" required>{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <hr class="mb-4">

                    <h5 class="mb-3 text-primary"><i class="fa-solid fa-location-dot me-2"></i>When & Where</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" for="date_found">Date Found <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-calendar-day"></i></span>
                                <input id="date_found" class="form-control" type="date" name="date_found" value="{{ old('date_found') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="location_found">Location Found <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-location-dot"></i></span>
                                <input id="location_found" class="form-control" name="location_found" value="{{ old('location_found') }}" placeholder="Building, room, or area" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="image">Photo (optional)</label>
                            <input id="image" class="form-control" type="file" name="image" accept="image/*">
                        </div>
                    </div>

                    <hr class="mb-4">

                    <div class="d-flex gap-2 justify-content-end flex-wrap">
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button class="btn btn-primary btn-lg" type="submit">
                            <i class="fa-solid fa-paper-plane me-2"></i>Submit Found Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
