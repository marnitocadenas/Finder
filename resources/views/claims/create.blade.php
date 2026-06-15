@extends('layouts.app')
@section('title','File a Claim')
@section('content')
<div class="claim-form-module">
    <div class="claims-hero">
        <div>
            <span class="module-eyebrow">Ownership request</span>
            <h1>File a Claim</h1>
            <p>Choose the found item, connect it to your lost report if available, and explain why it belongs to you.</p>
        </div>
        <a href="{{ route($claimRoute.'.index') }}" class="btn btn-light">
            <i class="fa-solid fa-arrow-left me-1"></i>Back
        </a>
    </div>

    @if($existingClaim)
        <div class="alert alert-warning student-duplicate-alert">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            You already have a pending claim for this found item.
            <a href="{{ route($claimRoute.'.show', $existingClaim) }}">Open existing claim</a>
        </div>
    @endif

    <form method="POST" action="{{ route($claimRoute.'.store') }}" enctype="multipart/form-data" class="claim-form-card">
        @csrf
        <div class="claim-form-section">
            <div>
                <span class="intake-step">Step 1</span>
                <h2>Item Match</h2>
                <p>Select the found item and optionally connect it with one of your lost reports.</p>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="found-item">Found Item</label>
                    <select id="found-item" class="form-select" name="found_item_id" required>
                        @foreach($foundItems as $item)
                            <option value="{{ $item->id }}" @selected($selectedFoundItem==$item->id)>{{ $item->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="lost-item">Related Lost Report</label>
                    <select id="lost-item" class="form-select" name="lost_item_id">
                        <option value="">None</option>
                        @foreach($lostItems as $item)
                            <option value="{{ $item->id }}">{{ $item->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="claim-form-section">
            <div>
                <span class="intake-step">Step 2</span>
                <h2>Ownership Proof</h2>
                <p>Describe unique marks, color, brand, contents, or anything only the owner would know.</p>
            </div>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label" for="claim-description">Why is this yours?</label>
                    <textarea id="claim-description" class="form-control" name="claim_description" rows="6" required minlength="30" data-character-counter="#claim-description-count" placeholder="Example: brand, color, scratches, contents, where you last had it, or a matching photo/receipt.">{{ old('claim_description') }}</textarea>
                    <div class="student-form-help">
                        <span id="claim-description-count">0</span>/1000 characters. Minimum 30 characters.
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label" for="proof-image">Proof Image</label>
                    <input id="proof-image" class="form-control" type="file" name="proof_image" accept="image/*" @required($proofRequired)>
                    <div class="student-form-help">
                        {{ $proofRequired ? 'Proof image is required.' : 'Optional: upload an old photo, receipt, or proof that helps staff verify ownership.' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="claim-form-actions">
            <button class="btn btn-primary" @disabled($existingClaim)>
                <i class="fa-solid fa-paper-plane me-1"></i>Submit Claim
            </button>
            <a href="{{ route($claimRoute.'.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
