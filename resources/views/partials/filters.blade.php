<form class="filter-panel mb-3">
    <div class="filter-field filter-field-wide">
        <label for="filter-q">Search</label>
        <div class="filter-input-icon">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input id="filter-q" class="form-control" name="q" value="{{ request('q') }}" placeholder="Search records">
        </div>
    </div>

    @isset($categories)
        <div class="filter-field">
            <label for="filter-category">Category</label>
            <select id="filter-category" class="form-select" name="category_id">
                <option value="">All categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id')==$category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
    @endisset

    @if(count($statuses ?? []))
        <div class="filter-field">
            <label for="filter-status">Status</label>
            <select id="filter-status" class="form-select" name="status">
                <option value="">All statuses</option>
                @foreach($statuses ?? [] as $status)
                    <option value="{{ $status }}" @selected(request('status')==$status)>{{ Illuminate\Support\Str::title(str_replace('_',' ',$status)) }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="filter-field">
        <label for="filter-from">From</label>
        <input id="filter-from" class="form-control" type="date" name="from" value="{{ request('from') }}">
    </div>

    <div class="filter-field">
        <label for="filter-to">To</label>
        <input id="filter-to" class="form-control" type="date" name="to" value="{{ request('to') }}">
    </div>

    <div class="filter-actions">
        <button class="btn btn-primary">
            <i class="fa-solid fa-filter me-1"></i>Apply
        </button>
        <a class="btn btn-light" href="{{ url()->current() }}">Reset</a>
    </div>
</form>
@php
    $activeFilters = collect(request()->only(['q','category_id','status','from','to']))->filter(fn($value) => filled($value));
@endphp
@if($activeFilters->isNotEmpty())
    <div class="filter-chip-row">
        @foreach($activeFilters as $key => $value)
            @php
                $label = match($key) {
                    'q' => 'Search',
                    'category_id' => 'Category',
                    'status' => 'Status',
                    'from' => 'From',
                    'to' => 'To',
                    default => Illuminate\Support\Str::title($key),
                };
                $display = $key === 'category_id' && isset($categories) ? optional($categories->firstWhere('id', $value))->name : Illuminate\Support\Str::title(str_replace('_', ' ', $value));
                $query = request()->except($key, 'page');
            @endphp
            <a href="{{ url()->current().(count($query) ? '?'.http_build_query($query) : '') }}">
                <span>{{ $label }}: {{ $display }}</span>
                <i class="fa-solid fa-xmark"></i>
            </a>
        @endforeach
    </div>
@endif
