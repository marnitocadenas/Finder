@extends('layouts.app')
@section('title', ($role ?? 'student') === 'staff' ? 'My Smart Matches' : 'Student Smart Matches')
@section('content')
<div class="matches-module student-match-module">
    <div class="matches-hero">
        <div>
            <span class="module-eyebrow">Match assistant</span>
            <h1>Smart Matches</h1>
            <p>Found items that may match your open lost reports based on category, date, location, and item details.</p>
        </div>
        <a href="{{ route(($role ?? 'student').'.lost-items.create') }}" class="btn btn-warning">
            <i class="fa-solid fa-plus me-1"></i>Report Lost
        </a>
    </div>

    <div class="matches-list">
        @forelse($matches as $group)
            <section class="match-card match-card-student">
                <div class="match-lost">
                    <span><i class="fa-solid {{ $group['lost']->category->icon ?? 'fa-magnifying-glass' }}"></i></span>
                    <div>
                        <small>Your lost report</small>
                        <strong title="{{ $group['lost']->title }}">{{ $group['lost']->title }}</strong>
                        <em title="{{ $group['lost']->location_lost }} • {{ optional($group['lost']->date_lost)->format('M d, Y') }}">{{ $group['lost']->location_lost }} &bull; {{ optional($group['lost']->date_lost)->format('M d, Y') }}</em>
                    </div>
                    <a href="{{ route(($role ?? 'student').'.lost-items.show', $group['lost']) }}" class="btn btn-sm btn-outline-primary">Open</a>
                </div>

                <div class="match-candidates">
                    @foreach($group['candidates'] as $candidate)
                        <div class="match-candidate">
                            <div class="match-score">{{ $candidate['score'] }}%</div>
                            <div>
                                <strong title="{{ $candidate['found']->title }}">{{ $candidate['found']->title }}</strong>
                                <small title="{{ $candidate['found']->location_found }} • {{ optional($candidate['found']->date_found)->format('M d, Y') }}">{{ $candidate['found']->location_found }} &bull; {{ optional($candidate['found']->date_found)->format('M d, Y') }}</small>
                                <div class="match-reasons">
                                    @foreach($candidate['reasons'] as $reason)
                                        <span>{{ $reason }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="match-actions">
                                <a href="{{ route(($claimRoute ?? 'student.claims').'.create', ['found_item_id' => $candidate['found']->id]) }}" class="btn btn-sm btn-primary">File Claim</a>
                                <form method="POST" action="{{ route(($role ?? 'student').'.watchlist.store', $candidate['found']) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary" title="Save item"><i class="fa-solid fa-bookmark"></i></button>
                                </form>
                                <form method="POST" action="{{ route('dismiss.match') }}" class="d-inline">
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
                <p>No smart matches yet. Updating your lost report with clearer details can improve matches.</p>
                <a href="{{ route(($role ?? 'student').'.lost-items.index') }}" class="btn btn-primary">Update Reports</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
