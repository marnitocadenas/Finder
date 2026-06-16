@extends('layouts.app')
@section('title','Saved Found Items')
@section('content')
<div class="browse-module">
    <div class="browse-hero">
        <div>
            <span class="module-eyebrow">Watchlist</span>
            <h1>Saved Items</h1>
            <p>Keep track of found items you want to review before filing a claim.</p>
        </div>
        <a href="{{ route(($role ?? 'student').'.browse') }}" class="btn btn-light">
            <i class="fa-solid fa-box-open me-1"></i>Browse Found
        </a>
    </div>

    <section class="browse-panel">
        <div class="browse-panel-header">
            <div>
                <span class="module-eyebrow">Saved</span>
                <h2>Watched Found Items</h2>
            </div>
            <span class="browse-result-count">{{ $watched->total() }} saved</span>
        </div>

        <div class="browse-grid">
            @forelse($watched as $watch)
                @php($item = $watch->foundItem)
                <article class="browse-card">
                    <button type="button" class="browse-card-media student-preview-trigger" data-image-preview="{{ $item->image ? asset('storage/'.$item->image) : '' }}" @disabled(!$item->image)>
                        @if($item->image)
                            <img src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->title }}">
                        @else
                            <div><i class="fa-solid {{ $item->category->icon ?? 'fa-box-open' }}"></i></div>
                        @endif
                        <x-status :status="$item->status" />
                    </button>
                    <div class="browse-card-body">
                        <div class="browse-card-heading">
                            <span class="browse-category"><i class="fa-solid {{ $item->category->icon ?? 'fa-tag' }}"></i>{{ $item->category->name ?? 'Uncategorized' }}</span>
                            <h3>{{ $item->title }}</h3>
                        </div>
                        <div class="browse-meta">
                            <span><i class="fa-solid fa-calendar-day"></i>{{ optional($item->date_found)->format('M d, Y') ?: 'No date' }}</span>
                            <span><i class="fa-solid fa-location-dot"></i>{{ $item->location_found }}</span>
                        </div>
                    </div>
                    <div class="browse-card-footer student-card-actions">
                        @if($item->status === 'unclaimed')
                            <a href="{{ route(($claimRoute ?? 'student.claims').'.create', ['found_item_id' => $item->id]) }}" class="btn btn-primary"><i class="fa-solid fa-file-signature me-1"></i>File Claim</a>
                        @endif
                        <form method="POST" action="{{ route(($role ?? 'student').'.watchlist.destroy', $item) }}" data-watchlist-toggle data-ajax>
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-secondary"><i class="fa-solid fa-bookmark-slash me-1"></i>Remove</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="browse-empty student-empty-action">
                    <i class="fa-solid fa-bookmark"></i>
                    <p>No saved found items yet.</p>
                    <a href="{{ route(($role ?? 'student').'.browse') }}" class="btn btn-primary">Browse Found Items</a>
                </div>
            @endforelse
        </div>

        <div class="browse-pagination">{{ $watched->links() }}</div>
    </section>
</div>
@include('partials.image-preview-modal')
@endsection
