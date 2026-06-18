@extends('layouts.app')
@section('title','Smart Matches')
@section('content')
<div class="matches-module student-match-module">
    <div class="matches-hero">
        <div>
            <span class="module-eyebrow">Smart matching</span>
            <h1>Possible Item Matches</h1>
            <p>Suggested lost-and-found pairings based on category, date proximity, location, and similar item wording.</p>
        </div>
        <a href="{{ route('admin.claims.index', ['status' => 'pending']) }}" class="btn btn-light">
            <i class="fa-solid fa-inbox me-1"></i>Review Claims
        </a>
    </div>

    <div class="matches-list">
        @forelse($matches as $group)
            <section class="match-card match-card-student">
                <div class="match-lost">
                    <span><i class="fa-solid {{ $group['lost']->category->icon ?? 'fa-magnifying-glass' }}"></i></span>
                    <div>
                        <small>Lost report</small>
                        <strong>{{ $group['lost']->title }}</strong>
                        <em>{{ $group['lost']->location_lost }} &bull; {{ optional($group['lost']->date_lost)->format('M d, Y') }}</em>
                    </div>
                    <a href="{{ route('admin.lost-items.edit', $group['lost']) }}" class="btn btn-sm btn-outline-primary">Open</a>
                </div>

                <div class="match-candidates">
                    @foreach($group['candidates'] as $candidate)
                        <div class="match-candidate">
                            <div class="match-score">{{ $candidate['score'] }}%</div>
                            <div>
                                <strong>{{ $candidate['found']->title }}</strong>
                                <small>{{ $candidate['found']->staff->name ?? 'Staff' }} &bull; {{ $candidate['found']->location_found }} &bull; {{ optional($candidate['found']->date_found)->format('M d, Y') }}</small>
                                <div class="match-reasons">
                                    @foreach($candidate['reasons'] as $reason)
                                        <span>{{ $reason }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="match-actions">
                                <a href="{{ route('admin.found-items.edit', $candidate['found']) }}" class="btn btn-sm btn-outline-primary">Inspect</a>
                                <form method="POST" action="{{ route('dismiss.match') }}">
                                    @csrf
                                    <input type="hidden" name="lost_item_id" value="{{ $group['lost']->id }}">
                                    <input type="hidden" name="found_item_id" value="{{ $candidate['found']->id }}">
                                    <button class="btn btn-sm btn-outline-secondary" title="Dismiss this match"><i class="fa-solid fa-xmark"></i></button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="empty-state student-empty-action">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <p>No possible matches yet.</p>
                <a href="{{ route('admin.claims.index', ['status' => 'pending']) }}" class="btn btn-primary">Review Claims</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
