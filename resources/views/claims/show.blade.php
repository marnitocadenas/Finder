@extends('layouts.app')
@section('title','Claim Receipt')
@section('content')
<div class="claim-detail-module">
    <div class="claims-hero">
        <div>
            <span class="module-eyebrow">Claim receipt</span>
            <h1>Claim #{{ $claim->id }}</h1>
            <p>{{ $claim->foundItem->title }} &bull; Submitted {{ $claim->created_at->format('M d, Y') }}</p>
        </div>
        <button onclick="window.print()" class="btn btn-light no-print">
            <i class="fa-solid fa-print me-1"></i>Print Receipt
        </button>
    </div>

    <div class="claim-detail-grid">
        <section class="claim-detail-card">
            <div class="claim-detail-heading">
                <div>
                    <span class="module-eyebrow">Review details</span>
                    <h2>Ownership Request</h2>
                </div>
                <x-status :status="$claim->status" />
            </div>

            <dl class="claim-detail-list">
                <div><dt>Student</dt><dd>{{ $claim->student->name }}</dd></div>
                <div><dt>Found Item</dt><dd>{{ $claim->foundItem->title }}</dd></div>
                <div><dt>Category</dt><dd>{{ $claim->foundItem->category->name ?? '-' }}</dd></div>
                <div><dt>Description</dt><dd>{{ $claim->claim_description }}</dd></div>
                <div><dt>Review Note</dt><dd>{{ $claim->review_note ?? '-' }}</dd></div>
                <div><dt>Reviewed By</dt><dd>{{ $claim->reviewer->name ?? '-' }}</dd></div>
                <div><dt>Reviewed At</dt><dd>{{ optional($claim->reviewed_at)->format('M d, Y h:i A') ?? '-' }}</dd></div>
            </dl>

            @if(in_array($role, ['admin','staff']) && $claim->status === 'pending')
                <form method="POST" action="{{ $role === 'staff' ? route('staff.claims.update', $claim) : route('admin.claims.update', $claim) }}" class="claim-review-form no-print">
                    @csrf @method('PUT')
                    <label class="form-label" for="review-note">Review note</label>
                    <textarea id="review-note" class="form-control" name="review_note" rows="4" placeholder="Optional reason or pickup instructions"></textarea>
                    <div class="claim-review-actions">
                        <button type="button" name="status" value="approved" class="btn btn-success" data-confirm-submit data-confirm-title="Approve claim" data-confirm-message="Approve this claim and mark the found item as claimed?" data-confirm-button="Approve" data-confirm-class="btn-success">
                            <i class="fa-solid fa-circle-check me-1"></i>Approve
                        </button>
                        <button type="button" name="status" value="rejected" class="btn btn-danger" data-confirm-submit data-confirm-title="Reject claim" data-confirm-message="Reject this claim request?" data-confirm-button="Reject">
                            <i class="fa-solid fa-circle-xmark me-1"></i>Reject
                        </button>
                    </div>
                </form>
            @endif
        </section>

        <aside class="claim-proof-card">
            <div class="claim-proof-header">
                <span class="module-eyebrow">Proof</span>
                <h2>Submitted Image</h2>
            </div>
            @if($claim->proof_image)
                <img class="lazy-thumb skeleton" loading="lazy" decoding="async" src="{{ asset('storage/'.$claim->proof_image) }}" alt="Claim proof" onload="this.classList.remove('skeleton')">
            @else
                <div class="claim-proof-empty">
                    <i class="fa-solid fa-image"></i>
                    <p>No proof image uploaded.</p>
                </div>
            @endif
        </aside>
    </div>
</div>
@include('partials.confirm-modal')
@endsection
