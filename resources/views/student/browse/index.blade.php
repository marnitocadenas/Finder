@extends('layouts.app')
@section('title','Browse Found Items')
@section('content')
<div class="browse-module">
    <div class="browse-hero">
        <div>
            <span class="module-eyebrow">{{ ($role ?? 'student') === 'staff' ? 'Staff search' : 'Student search' }}</span>
            <h1>Browse Found Items</h1>
            <p>Search verified found items posted by staff and file a claim when you recognize your belongings.</p>
        </div>
        <a href="{{ route(($claimRoute ?? 'student.claims').'.index') }}" class="btn btn-light">
            <i class="fa-solid fa-file-signature me-1"></i>My Claims
        </a>
    </div>

    <div class="browse-stat-grid">
        @foreach($browseStats as $stat)
            <div class="browse-stat-card browse-stat-{{ $stat['tone'] }}">
                <span><i class="fa-solid {{ $stat['icon'] }}"></i></span>
                <div>
                    <small>{{ $stat['label'] }}</small>
                    <strong>{{ $stat['value'] }}</strong>
                </div>
            </div>
        @endforeach
    </div>

    <section class="browse-panel">
        <div class="browse-panel-header">
            <div>
                <span class="module-eyebrow">Catalog</span>
                <h2>Found Item Listings</h2>
            </div>
            <span class="browse-result-count">{{ $items->total() }} results</span>
        </div>

        @include('partials.filters', ['statuses' => ['unclaimed', 'claimed', 'turned_over']])

        <form class="student-browse-tools">
            @foreach(request()->except(['sort','available_only','page']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <label class="student-toggle">
                <input type="checkbox" name="available_only" value="1" @checked(request()->boolean('available_only')) onchange="this.form.submit()">
                <span></span>
                Available only
            </label>
            <select class="form-select form-select-sm" name="sort" onchange="this.form.submit()">
                <option value="">Newest first</option>
                <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
            </select>
        </form>

        <div class="browse-grid">
            @forelse($items as $item)
                <article class="browse-card">
                    <button type="button" class="browse-card-media student-preview-trigger" data-image-preview="{{ $item->image ? asset('storage/'.$item->image) : '' }}" @disabled(!$item->image)>
                        @if($item->image)
                            <img src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->title }}">
                        @else
                            <div>
                                <i class="fa-solid {{ $item->category->icon ?? 'fa-box-open' }}"></i>
                            </div>
                        @endif
                        <x-status :status="$item->status" />
                    </button>
                    <div class="browse-card-body">
                        <div class="browse-card-heading">
                            <span class="browse-category">
                                <i class="fa-solid {{ $item->category->icon ?? 'fa-tag' }}"></i>
                                {{ $item->category->name ?? 'Uncategorized' }}
                            </span>
                            <h3>{{ $item->title }}</h3>
                        </div>
                        <div class="browse-meta">
                            <span><i class="fa-solid fa-calendar-day"></i>{{ optional($item->date_found)->format('M d, Y') ?: 'No date' }}</span>
                            <span><i class="fa-solid fa-location-dot"></i>{{ $item->location_found }}</span>
                        </div>
                        <p>{{ Illuminate\Support\Str::limit($item->description, 135) }}</p>
                    </div>
                    <div class="browse-card-footer">
                        <button type="button" class="btn btn-outline-primary student-detail-button"
                            data-student-preview-title="{{ $item->title }}"
                            data-student-preview-category="{{ $item->category->name ?? 'Uncategorized' }}"
                            data-student-preview-date="{{ optional($item->date_found)->format('M d, Y') ?: 'No date' }}"
                            data-student-preview-location="{{ $item->location_found }}"
                            data-student-preview-description="{{ $item->description }}"
                            data-student-preview-image="{{ $item->image ? asset('storage/'.$item->image) : '' }}"
                            data-student-preview-claim="{{ route(($claimRoute ?? 'student.claims').'.create', ['found_item_id' => $item->id]) }}">
                            <i class="fa-solid fa-eye me-1"></i>Preview
                        </button>
                        @if(in_array($item->id, $existingClaimFoundIds ?? []))
                            <a href="{{ route(($claimRoute ?? 'student.claims').'.index', ['q' => $item->title]) }}" class="btn btn-light">
                                <i class="fa-solid fa-clock me-1"></i>Pending Claim
                            </a>
                        @elseif($item->status === 'unclaimed')
                            <a href="{{ route(($claimRoute ?? 'student.claims').'.create', ['found_item_id' => $item->id]) }}" class="btn btn-primary">
                                <i class="fa-solid fa-file-signature me-1"></i>File Claim
                            </a>
                        @else
                            <span class="browse-unavailable">
                                <i class="fa-solid fa-lock"></i>Claim unavailable
                            </span>
                        @endif
                        @if(in_array($item->id, $watchedIds ?? []))
                            <form method="POST" action="{{ route(($role ?? 'student').'.watchlist.destroy', $item) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-secondary"><i class="fa-solid fa-bookmark-slash me-1"></i>Unsave</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route(($role ?? 'student').'.watchlist.store', $item) }}">
                                @csrf
                                <button class="btn btn-outline-secondary"><i class="fa-solid fa-bookmark me-1"></i>Save</button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <div class="browse-empty">
                    <i class="fa-solid fa-box-open"></i>
                    <p>No found items match your filters.</p>
                </div>
            @endforelse
        </div>

        <div class="browse-pagination">
            {{ $items->links() }}
        </div>
    </section>
</div>
<div class="modal fade" id="studentItemPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content student-preview-modal">
            <div class="modal-header">
                <h2 class="modal-title h5" data-student-preview-modal-title>Found Item</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="student-preview-grid">
                    <div class="student-preview-image"><img data-student-preview-modal-image src="" alt=""></div>
                    <div>
                        <dl class="student-preview-list">
                            <div><dt>Category</dt><dd data-student-preview-modal-category></dd></div>
                            <div><dt>Date Found</dt><dd data-student-preview-modal-date></dd></div>
                            <div><dt>Location</dt><dd data-student-preview-modal-location></dd></div>
                            <div><dt>Description</dt><dd data-student-preview-modal-description></dd></div>
                        </dl>
                        <a data-student-preview-modal-claim class="btn btn-primary" href="#"><i class="fa-solid fa-file-signature me-1"></i>File Claim</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('partials.image-preview-modal')
@endsection
