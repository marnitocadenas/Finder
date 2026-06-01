@extends('layouts.app')
@section('title', $role === 'staff' ? 'My Found Items' : 'Found Items Management')
@section('content')
<div class="found-module">
    @if($role === 'admin')
        <div class="found-hero">
            <div>
                <span class="module-eyebrow">Admin inventory</span>
                <h1>Found Items</h1>
                <p>Track recovered belongings, claim status, and turned-over records across the campus inventory.</p>
            </div>
            <a href="{{ route('admin.reports') }}" class="btn btn-warning">
                <i class="fa-solid fa-chart-column me-1"></i>Open Reports
            </a>
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
                <span class="module-eyebrow">Staff inventory</span>
                <h1>My Found Items</h1>
                <p>Post recovered belongings, update claim status, and keep item records ready for students.</p>
            </div>
            <a href="{{ route('staff.found-items.create') }}" class="btn btn-warning">
                <i class="fa-solid fa-plus me-1"></i>Post Found Item
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

        <div class="found-table-wrap">
            <table class="table found-table align-middle">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Staff</th>
                        <th>Category</th>
                        <th>Date Found</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>
                                <div class="found-item-cell">
                                    @if($item->image)
                                        <img class="found-thumb lazy-thumb skeleton" loading="lazy" decoding="async" src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->title }}" onload="this.classList.remove('skeleton')">
                                    @else
                                        <span class="found-thumb-placeholder"><i class="fa-solid {{ $item->category->icon ?? 'fa-box-open' }}"></i></span>
                                    @endif
                                    <div>
                                        <strong>{{ $item->title }}</strong>
                                        <small><i class="fa-solid fa-location-dot"></i>{{ $item->location_found }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $item->staff->name ?? auth()->user()->name }}</td>
                            <td>
                                <span class="found-category">
                                    <i class="fa-solid {{ $item->category->icon ?? 'fa-tag' }}"></i>{{ $item->category->name ?? '-' }}
                                </span>
                            </td>
                            <td>{{ optional($item->date_found)->format('M d, Y') }}</td>
                            <td>
                                <x-status :status="$item->status" />
                                @if($item->trashed())
                                    <span class="badge bg-dark ms-1">Deleted</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($item->trashed() && $role === 'admin')
                                    <form method="POST" action="{{ route('admin.found-items.restore', $item->id) }}" class="d-inline">
                                        @csrf @method('PUT')
                                        <button type="button" class="btn btn-sm btn-outline-success" data-confirm-submit data-confirm-title="Restore found item" data-confirm-message="Restore this found item?" data-confirm-button="Restore" data-confirm-class="btn-success">
                                            <i class="fa-solid fa-rotate-left me-1"></i>Restore
                                        </button>
                                    </form>
                                @else
                                    <a class="btn btn-sm btn-outline-primary" href="{{ $role === 'staff' ? route('staff.found-items.edit', $item) : route('admin.found-items.edit', $item) }}">
                                        <i class="fa-solid fa-pen-to-square me-1"></i>View/Edit
                                    </a>
                                    <form method="POST" action="{{ $role === 'staff' ? route('staff.found-items.destroy', $item) : route('admin.found-items.destroy', $item) }}" class="d-inline">
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
                            <td colspan="6">
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
    </section>
</div>
@include('partials.confirm-modal')
@endsection
