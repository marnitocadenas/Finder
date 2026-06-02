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
                <div><dt>Pickup Instruction</dt><dd>{{ $claim->pickup_instruction ?? '-' }}</dd></div>
                <div><dt>Reviewed By</dt><dd>{{ $claim->reviewer->name ?? '-' }}</dd></div>
                <div><dt>Reviewed At</dt><dd>{{ optional($claim->reviewed_at)->format('M d, Y h:i A') ?? '-' }}</dd></div>
                <div><dt>Released By</dt><dd>{{ $claim->releaser->name ?? '-' }}</dd></div>
                <div><dt>Released At</dt><dd>{{ optional($claim->released_at)->format('M d, Y h:i A') ?? '-' }}</dd></div>
            </dl>

            @if(in_array($role, ['admin','staff']) && $claim->status === 'pending')
                <form method="POST" action="{{ $role === 'staff' ? route('staff.claims.update', $claim) : route('admin.claims.update', $claim) }}" class="claim-review-form no-print">
                    @csrf @method('PUT')
                    <label class="form-label" for="review-note">Review note</label>
                    <textarea id="review-note" class="form-control" name="review_note" rows="4" placeholder="Optional reason or pickup instructions"></textarea>
                    <label class="form-label mt-3" for="pickup-instruction">Pickup instruction</label>
                    <textarea id="pickup-instruction" class="form-control" name="pickup_instruction" rows="3" placeholder="Where and when the student can claim the item"></textarea>
                    <div class="claim-review-actions">
                        <button type="button" name="status" value="approved" class="btn btn-success" data-confirm-submit data-confirm-title="Approve claim" data-confirm-message="Approve this claim and mark the found item as claimed?" data-confirm-button="Approve" data-confirm-class="btn-success">
                            <i class="fa-solid fa-circle-check me-1"></i>Approve
                        </button>
                        <button type="button" name="status" value="rejected" class="btn btn-danger" data-confirm-submit data-confirm-title="Reject claim" data-confirm-message="Reject this claim request?" data-confirm-button="Reject">
                            <i class="fa-solid fa-circle-xmark me-1"></i>Reject
                        </button>
                    </div>
                </form>
                @if($role === 'staff')
                    <form method="POST" action="{{ route('staff.claims.request-info', $claim) }}" class="claim-review-form no-print">
                        @csrf
                        <label class="form-label" for="request-info-message">Request more information</label>
                        <select class="form-select mb-2" data-template-target="#request-info-message">
                            <option value="">Choose a message template</option>
                            <option value="Please upload a clearer proof image or describe a unique detail about the item.">Need clearer proof</option>
                            <option value="Please provide your student ID and the approximate date you lost the item.">Need student verification</option>
                            <option value="Please describe the item's brand, color, or identifying marks before we continue review.">Need item details</option>
                        </select>
                        <textarea id="request-info-message" class="form-control" name="message" rows="3" placeholder="Message to student" required></textarea>
                        <div class="claim-review-actions">
                            <button class="btn btn-outline-primary"><i class="fa-solid fa-message me-1"></i>Send Request</button>
                        </div>
                    </form>
                @endif
            @endif

            @if(in_array($role, ['admin','staff']) && $claim->status === 'approved' && !$claim->released_at)
                <form method="POST" action="{{ $role === 'staff' ? route('staff.claims.release', $claim) : route('admin.claims.release', $claim) }}" class="claim-release-form no-print">
                    @csrf @method('PUT')
                    <button type="button" class="btn btn-primary" data-confirm-submit data-confirm-title="Release item" data-confirm-message="Mark this approved claim as released to the student?" data-confirm-button="Release" data-confirm-class="btn-primary">
                        <i class="fa-solid fa-handshake me-1"></i>Mark Released
                    </button>
                </form>
            @endif

            <div class="claim-timeline">
                <div class="claim-timeline-item is-done"><i class="fa-solid fa-paper-plane"></i><span>Submitted</span><small>{{ $claim->created_at->format('M d, Y h:i A') }}</small></div>
                <div class="claim-timeline-item {{ $claim->reviewed_at ? 'is-done' : '' }}"><i class="fa-solid fa-clipboard-check"></i><span>Reviewed</span><small>{{ optional($claim->reviewed_at)->format('M d, Y h:i A') ?? 'Waiting' }}</small></div>
                <div class="claim-timeline-item {{ $claim->released_at ? 'is-done' : '' }}"><i class="fa-solid fa-handshake"></i><span>Released</span><small>{{ optional($claim->released_at)->format('M d, Y h:i A') ?? 'Not released' }}</small></div>
            </div>
        </section>

        @php($sameEvidenceImage = $claim->foundItem->image && $claim->proof_image && $claim->foundItem->image === $claim->proof_image)
        <aside class="claim-proof-card">
            <div class="claim-proof-header">
                <span class="module-eyebrow">Proof</span>
                <h2>Evidence Images</h2>
            </div>
            @if($sameEvidenceImage)
                <div class="claim-proof-compare">
                    <strong>Found item and submitted proof use the same image</strong>
                    <button type="button" class="image-preview-button" data-image-preview="{{ asset('storage/'.$claim->proof_image) }}">
                        <img src="{{ asset('storage/'.$claim->proof_image) }}" alt="Found item and claim proof">
                    </button>
                </div>
            @elseif($claim->foundItem->image)
                <div class="claim-proof-compare">
                    <strong>Found item photo</strong>
                    <button type="button" class="image-preview-button" data-image-preview="{{ asset('storage/'.$claim->foundItem->image) }}">
                        <img src="{{ asset('storage/'.$claim->foundItem->image) }}" alt="{{ $claim->foundItem->title }}">
                    </button>
                </div>
            @endif
            @if($claim->proof_image && !$sameEvidenceImage)
                <div class="claim-proof-compare">
                    <strong>Submitted proof image</strong>
                    <button type="button" class="image-preview-button" data-image-preview="{{ asset('storage/'.$claim->proof_image) }}">
                        <img src="{{ asset('storage/'.$claim->proof_image) }}" alt="Claim proof">
                    </button>
                </div>
            @elseif(!$claim->proof_image)
                <div class="claim-proof-empty">
                    <i class="fa-solid fa-image"></i>
                    <p>No proof image uploaded.</p>
                </div>
            @endif
        </aside>
    </div>
</div>
@include('partials.confirm-modal')
@include('partials.image-preview-modal')
@endsection
