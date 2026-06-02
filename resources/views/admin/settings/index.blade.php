@extends('layouts.app')
@section('title','Admin Settings')
@section('content')
<div class="settings-module">
    <div class="settings-hero">
        <div>
            <span class="module-eyebrow">Operations</span>
            <h1>Admin Settings</h1>
            <p>Control campus pickup details, claim proof behavior, report aging, and admin contact information.</p>
        </div>
    </div>

    <section class="settings-panel">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="settings-form">
            @csrf @method('PUT')
            <div>
                <label class="form-label">Pickup Location</label>
                <input class="form-control" name="pickup_location" value="{{ old('pickup_location', $settings['pickup_location']) }}" required>
            </div>
            <div>
                <label class="form-label">Auto-Close Lost Reports After</label>
                <div class="input-group">
                    <input class="form-control" type="number" min="1" max="365" name="auto_close_days" value="{{ old('auto_close_days', $settings['auto_close_days']) }}" required>
                    <span class="input-group-text">days</span>
                </div>
            </div>
            <div>
                <label class="form-label">Admin Contact Email</label>
                <input class="form-control" type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}" required>
            </div>
            <label class="settings-toggle">
                <input type="checkbox" name="claim_proof_required" value="1" @checked(old('claim_proof_required', $settings['claim_proof_required']) == '1')>
                <span></span>
                <strong>Require proof image for claims</strong>
            </label>
            <div class="settings-actions">
                <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save Settings</button>
            </div>
        </form>
    </section>
</div>
@endsection
