@extends('layouts.app')
@section('title', $role === 'student' ? 'My Found Reports' : ($role === 'staff' ? 'My Found Items' : 'Found Items Management'))
@section('content')
<div class="found-module">
    @if($role === 'admin')
        <div class="found-hero">
            <div>
                <span class="module-eyebrow">Admin inventory</span>
                <h1>Found Items</h1>
                <p>Track recovered belongings, claim status, and turned-over records across the campus inventory.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.found-items.create') }}" class="btn btn-primary">
                    <i class="fa-solid fa-plus me-1"></i>Post Found
                </a>
                <a href="{{ route('admin.reports') }}" class="btn btn-warning">
                    <i class="fa-solid fa-chart-column me-1"></i>Open Reports
                </a>
            </div>
        </div>

        <div class="found-stat-grid">
            @foreach($foundStats ?? [] as $stat)
                <div class="found-stat-card found-stat-{{ $stat['tone'] }}">
                    <span><i class="fa-solid {{ $stat['icon'] }}"></i></span>
                    <div>
                        <small>{{ $stat['label'] }}</small>
                        <strong>{{ $stat['value'] }}</strong>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="found-hero">
            <div>
                <span class="module-eyebrow">{{ $role === 'student' ? 'Student reports' : 'Staff inventory' }}</span>
                <h1>{{ $role === 'student' ? 'My Found Reports' : 'My Found Items' }}</h1>
                <p>{{ $role === 'student' ? 'Track found items you reported and keep details updated.' : 'Post recovered belongings, update claim status, and keep item records ready for students.' }}</p>
            </div>
            <a href="{{ route($role.'.found-items.create') }}" class="btn btn-warning">
                <i class="fa-solid fa-plus me-1"></i>{{ $role === 'student' ? 'Report Found Item' : 'Post Found Item' }}
            </a>
        </div>
    @endif

    <section class="found-panel">
        <div class="found-panel-header">
            <div>
                <span class="module-eyebrow">Inventory</span>
                <h2>Records</h2>
            </div>
            @if($role === 'admin')
                <div class="found-tabs">
                    <a class="{{ request('deleted') ? '' : 'active' }}" href="{{ route('admin.found-items.index') }}">Active</a>
                    <a class="{{ request('deleted') === 'trashed' ? 'active' : '' }}" href="{{ route('admin.found-items.index', ['deleted' => 'trashed']) }}">Deleted</a>
                    <a class="{{ request('deleted') === 'all' ? 'active' : '' }}" href="{{ route('admin.found-items.index', ['deleted' => 'all']) }}">All</a>
                </div>
            @endif
        </div>

        @include('partials.filters', ['statuses' => ['unclaimed','claimed','turned_over']])

        @if($role === 'staff')
            <div class="staff-view-actions">
                <a class="btn btn-light btn-sm" href="{{ route('staff.matches') }}"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Find Matches</a>
                <a class="btn btn-warning btn-sm" href="{{ route('staff.found-items.create') }}"><i class="fa-solid fa-plus me-1"></i>Post Found</a>
            </div>
        @endif

        @if($role === 'admin')
            <form id="found-bulk-form" class="bulk-toolbar" method="POST" action="{{ route('admin.found-items.bulk') }}">
                @csrf
                <select name="action" class="form-select form-select-sm" required>
                    <option value="">Bulk action</option>
                    <option value="unclaimed">Mark unclaimed</option>
                    <option value="claimed">Mark claimed</option>
                    <option value="turned_over">Mark turned over</option>
                    <option value="delete">Delete selected</option>
                    <option value="restore">Restore selected</option>
                </select>
                <button class="btn btn-sm btn-primary" type="submit"><i class="fa-solid fa-bolt me-1"></i>Apply</button>
            </form>
        @endif

        <div class="found-table-wrap">
            <table class="table found-table excel-found-table {{ $role === 'staff' ? 'staff-found-table' : '' }} align-middle">
                <thead>
                    <tr>
                        @if($role === 'admin')<th><input type="checkbox" data-check-all></th>@endif
                        <th>Item</th>
                        @if(!($personalView ?? false))<th>Reported By</th>@endif
                        <th>Category</th>
                        <th>Date Found</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            @if($role === 'admin')<td data-label="Select"><input type="checkbox" name="ids[]" value="{{ $item->id }}" form="found-bulk-form"></td>@endif
                            <td data-label="Item">
                                <div class="found-item-cell">
                                    @if($item->image)
                                        <img class="found-thumb" src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->title }}">
                                    @else
                                        <span class="found-thumb-placeholder"><i class="fa-solid {{ $item->category->icon ?? 'fa-box-open' }}"></i></span>
                                    @endif
                                    <div>
                                        <strong>{{ $item->title }}</strong>
                                        <small><i class="fa-solid fa-location-dot"></i>{{ $item->location_found }}</small>
                                    </div>
                                </div>
                            </td>
                            @if(!($personalView ?? false))
                            <td data-label="Reported By">{{ $item->staff->name ?? ($item->guest_name ? $item->guest_name.' (Guest)' : auth()->user()->name) }}</td>
                            @endif
                            <td data-label="Category">
                                <span class="found-category">
                                    <i class="fa-solid {{ $item->category->icon ?? 'fa-tag' }}"></i>{{ $item->category->name ?? '-' }}
                                </span>
                            </td>
                            <td data-label="Date Found">{{ optional($item->date_found)->format('M d, Y') }}</td>
                            <td data-label="Status">
                                <x-status :status="$item->status" />
                                @if($item->trashed())
                                    <span class="badge bg-dark ms-1">Deleted</span>
                                @endif
                            </td>
                            <td data-label="Actions" class="text-end">
                                @if($item->trashed() && $role === 'admin')
                                    <form method="POST" action="{{ route('admin.found-items.restore', $item->id) }}" class="d-inline">
                                        @csrf @method('PUT')
                                        <button type="button" class="btn btn-sm btn-outline-success" data-confirm-submit data-confirm-title="Restore found item" data-confirm-message="Restore this found item?" data-confirm-button="Restore" data-confirm-class="btn-success">
                                            <i class="fa-solid fa-rotate-left me-1"></i>Restore
                                        </button>
                                    </form>
                                @else
                                    <a class="btn btn-sm btn-outline-primary" href="{{ $role === 'student' ? route('student.found-items.edit', $item) : ($role === 'staff' ? route('staff.found-items.edit', $item) : route('admin.found-items.edit', $item)) }}">
                                        <i class="fa-solid fa-pen-to-square me-1"></i>View/Edit
                                    </a>
                                    @if($role === 'staff')
                                        <form method="POST" action="{{ route('staff.found-items.status', $item) }}" class="d-inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="{{ $item->status === 'unclaimed' ? 'claimed' : 'unclaimed' }}">
                                            <button class="btn btn-sm btn-outline-success">
                                                <i class="fa-solid fa-arrows-rotate me-1"></i>{{ $item->status === 'unclaimed' ? 'Mark Claimed' : 'Reopen' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('staff.found-items.status', $item) }}" class="d-inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="turned_over">
                                            <button class="btn btn-sm btn-outline-secondary">
                                                <i class="fa-solid fa-building-columns me-1"></i>Turn Over
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ $role === 'student' ? route('student.found-items.destroy', $item) : ($role === 'staff' ? route('staff.found-items.destroy', $item) : route('admin.found-items.destroy', $item)) }}" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-confirm-submit data-confirm-title="Delete found item" data-confirm-message="Move this found item to deleted records?" data-confirm-button="Delete">
                                            <i class="fa-solid fa-trash-can me-1"></i>Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $role === 'admin' ? 7 : (($personalView ?? false) ? 5 : 6) }}">
                                <div class="found-empty">
                                    <i class="fa-solid fa-box-open"></i>
                                    <p>No found items found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $items->links() }}
        </div>

        @if($role === 'staff')
            <div class="staff-preview-header">
                <div>
                    <span class="module-eyebrow">Visual preview</span>
                    <h2>Item Cards</h2>
                </div>
                <small>Click an image to preview it.</small>
            </div>
            <div class="staff-card-grid">
                @forelse($items as $item)
                    <article class="staff-item-card">
                        <button type="button" class="staff-card-photo" data-image-preview="{{ $item->image ? asset('storage/'.$item->image) : '' }}" @disabled(!$item->image)>
                            @if($item->image)
                                <img src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->title }}">
                            @else
                                <i class="fa-solid {{ $item->category->icon ?? 'fa-box-open' }}"></i>
                            @endif
                        </button>
                        <div class="staff-item-card-body">
                            <div class="staff-card-meta">
                                <small>Category</small>
                                <span><i class="fa-solid {{ $item->category->icon ?? 'fa-tag' }}"></i>{{ $item->category->name ?? '-' }}</span>
                            </div>
                            <div class="staff-card-meta">
                                <small>Item</small>
                                <strong>{{ $item->title }}</strong>
                            </div>
                            <div class="staff-card-meta">
                                <small>Location</small>
                                <span><i class="fa-solid fa-location-dot"></i>{{ $item->location_found }}</span>
                            </div>
                            <div class="staff-card-status"><x-status :status="$item->status" /></div>
                            <div class="staff-card-actions">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('staff.found-items.edit', $item) }}"><i class="fa-solid fa-pen-to-square me-1"></i>Open</a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('staff.matches') }}"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Matches</a>
                            </div>
                        </div>
                    </article>
                @empty
                @endforelse
            </div>
            @include('partials.image-preview-modal')
        @endif
    </section>
</div>
@include('partials.confirm-modal')
@endsection
