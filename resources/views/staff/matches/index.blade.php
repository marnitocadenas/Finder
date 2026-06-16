@extends('layouts.app')
@section('title','Staff Smart Matches')
@section('content')
<div class="matches-module">
    <div class="matches-hero">
        <div>
            <span class="module-eyebrow">Staff assistant</span>
            <h1>Smart Matches</h1>
            <p>Compare your unclaimed found items with active student lost reports before claims arrive.</p>
        </div>
        <a href="{{ route('staff.found-items.create') }}" class="btn btn-warning">
            <i class="fa-solid fa-plus me-1"></i>Post Found
        </a>
    </div>

    <div class="matches-list">
        @forelse($matches as $group)
            <section class="match-card">
                <div class="match-lost">
                    <span><i class="fa-solid {{ $group['found']->category->icon ?? 'fa-box-open' }}"></i></span>
                    <div>
                        <small>Your found item</small>
                        <strong>{{ $group['found']->title }}</strong>
                        <em>{{ $group['found']->location_found }} &bull; {{ optional($group['found']->date_found)->format('M d, Y') }}</em>
                    </div>
                    <a href="{{ route('staff.found-items.show', $group['found']) }}" class="btn btn-sm btn-outline-primary">Open</a>
                </div>

                <div class="match-candidates">
                    @foreach($group['candidates'] as $candidate)
                        <div class="match-candidate">
                            <div class="match-score">{{ $candidate['score'] }}%</div>
                            <div>
                                <strong>{{ $candidate['lost']->title }}</strong>
                                <small>{{ $candidate['lost']->user->name ?? 'Student' }} &bull; {{ $candidate['lost']->location_lost }} &bull; {{ optional($candidate['lost']->date_lost)->format('M d, Y') }}</small>
                                <div class="match-reasons">
                                    @foreach($candidate['reasons'] as $reason)
                                        <span>{{ $reason }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <a href="{{ route('staff.lost-reports.show', $candidate['lost']) }}" class="btn btn-sm btn-outline-primary">Inspect</a>
                            <form method="POST" action="{{ route('dismiss.match') }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="found_item_id" value="{{ $group['found']->id }}">
                                <input type="hidden" name="lost_item_id" value="{{ $candidate['lost']->id }}">
                                <button class="btn btn-sm btn-outline-secondary" title="Dismiss this match"><i class="fa-solid fa-xmark"></i></button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="empty-state">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <p>No matches for your found items yet.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
