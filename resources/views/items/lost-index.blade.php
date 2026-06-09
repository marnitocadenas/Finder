@extends('layouts.app')
@section('title', $role === 'student' ? 'My Lost Reports' : 'Lost Items Management')
@section('content')
<div class="lost-module">
    @if($role === 'admin')
        <div class="lost-hero">
            <div>
                <span class="module-eyebrow">Admin records</span>
                <h1>Lost Items</h1>
                <p>Review lost reports, monitor recovery status, and manage deleted records from one workspace.</p>
            </div>
            <a href="{{ route('admin.reports') }}" class="btn btn-warning">
                <i class="fa-solid fa-chart-column me-1"></i>Open Reports
            </a>
        </div>

        <div class="lost-stat-grid">
            @foreach($lostStats ?? [] as $stat)
                <div class="lost-stat-card lost-stat-{{ $stat['tone'] }}">
                    <span><i class="fa-solid {{ $stat['icon'] }}"></i></span>
                    <div>
                        <small>{{ $stat['label'] }}</small>
                        <strong>{{ $stat['value'] }}</strong>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="lost-hero">
            <div>
                <span class="module-eyebrow">{{ $role === 'student' ? 'Student reports' : 'Staff review' }}</span>
                <h1>{{ $role === 'student' ? 'My Lost Reports' : 'Lost Reports' }}</h1>
                <p>{{ $role === 'student' ? 'Track your submitted reports and keep details updated while the item is missing.' : 'Browse student lost reports and help match them with found items.' }}</p>
            </div>
            @if($role === 'student')
                <a href="{{ route('student.lost-items.create') }}" class="btn btn-warning">
                    <i class="fa-solid fa-plus me-1"></i>Report Lost Item
                </a>
            @endif
        </div>
    @endif

    <section class="lost-panel">
        <div class="lost-panel-header">
            <div>
                <span class="module-eyebrow">Report directory</span>
                <h2>Records</h2>
            </div>
            @if($role === 'admin')
                <div class="lost-tabs">
                    <a class="{{ request('deleted') ? '' : 'active' }}" href="{{ route('admin.lost-items.index') }}">Active</a>
                    <a class="{{ request('deleted') === 'trashed' ? 'active' : '' }}" href="{{ route('admin.lost-items.index', ['deleted' => 'trashed']) }}">Deleted</a>
                    <a class="{{ request('deleted') === 'all' ? 'active' : '' }}" href="{{ route('admin.lost-items.index', ['deleted' => 'all']) }}">All</a>
                </div>
            @endif
        </div>

        @include('partials.filters', ['statuses' => ['lost','found','closed']])

        @if($role === 'admin')
            <form id="lost-bulk-form" class="bulk-toolbar" method="POST" action="{{ route('admin.lost-items.bulk') }}">
                @csrf
                <select name="action" class="form-select form-select-sm" required>
                    <option value="">Bulk action</option>
                    <option value="lost">Mark lost</option>
                    <option value="found">Mark found</option>
                    <option value="closed">Mark closed</option>
                    <option value="delete">Delete selected</option>
                    <option value="restore">Restore selected</option>
                </select>
                <button class="btn btn-sm btn-primary" type="submit"><i class="fa-solid fa-bolt me-1"></i>Apply</button>
            </form>
        @endif

        <div class="lost-table-wrap">
            <table class="table lost-table {{ $role === 'student' ? 'student-lost-table' : '' }} {{ $role === 'staff' ? 'staff-lost-table' : '' }} {{ $role !== 'student' ? 'excel-lost-table' : '' }} align-middle">
                <thead>
                    <tr>
                        @if($role === 'admin')<th><input type="checkbox" data-check-all></th>@endif
                        <th>Item</th>
                        @if($role !== 'student')<th>Student</th>@endif
                        <th>Category</th>
                        <th>Date Lost</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            @if($role === 'admin')<td data-label="Select"><input type="checkbox" name="ids[]" value="{{ $item->id }}" form="lost-bulk-form"></td>@endif
                            <td data-label="Item">
                                <div class="lost-item-cell">
                                    @if($item->image)
                                        <img class="lost-thumb" src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->title }}">
                                    @else
                                        <span class="lost-thumb-placeholder"><i class="fa-solid {{ $item->category->icon ?? 'fa-box' }}"></i></span>
                                    @endif
                                    <div>
                                        <strong>{{ $item->title }}</strong>
                                        <small><i class="fa-solid fa-location-dot"></i>{{ $item->location_lost }}</small>
                                    </div>
                                </div>
                            </td>
                            @if($role !== 'student')
                                <td data-label="Student">{{ $item->user->name ?? auth()->user()->name }}</td>
                            @endif
                            <td data-label="Category">
                                <span class="lost-category">
                                    <i class="fa-solid {{ $item->category->icon ?? 'fa-tag' }}"></i>{{ $item->category->name ?? '-' }}
                                </span>
                            </td>
                            <td data-label="Date Lost">{{ optional($item->date_lost)->format('M d, Y') }}</td>
                            <td data-label="Status">
                                <x-status :status="$item->status" />
                                @if($item->trashed())
                                    <span class="badge bg-dark ms-1">Deleted</span>
                                @endif
                            </td>
                            <td data-label="Actions" class="{{ $role === 'student' ? 'student-lost-actions-cell' : '' }}">
                                @if($role === 'staff')
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('staff.lost-reports.show', $item) }}">
                                        <i class="fa-solid fa-eye me-1"></i>View
                                    </a>
                                @elseif($item->trashed() && $role === 'admin')
                                    <form method="POST" action="{{ route('admin.lost-items.restore', $item->id) }}" class="d-inline">
                                        @csrf @method('PUT')
                                        <button type="button" class="btn btn-sm btn-outline-success" data-confirm-submit data-confirm-title="Restore lost report" data-confirm-message="Restore this lost report?" data-confirm-button="Restore" data-confirm-class="btn-success">
                                            <i class="fa-solid fa-rotate-left me-1"></i>Restore
                                        </button>
                                    </form>
                                @else
                                    @if($role === 'student')<div class="student-lost-actions">@endif
                                    <a class="btn btn-sm btn-outline-primary" href="{{ $role === 'student' ? route('student.lost-items.edit', $item) : route('admin.lost-items.edit', $item) }}">
                                        <i class="fa-solid fa-pen-to-square me-1"></i>View/Edit
                                    </a>
                                    @if($role === 'student')
                                        <form method="POST" action="{{ route('student.lost-items.status', $item) }}" class="d-inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="{{ $item->status === 'lost' ? 'found' : 'lost' }}">
                                            <button class="btn btn-sm btn-outline-success">
                                                <i class="fa-solid fa-arrows-rotate me-1"></i>{{ $item->status === 'lost' ? 'Mark Found' : 'Reopen' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('student.lost-items.status', $item) }}" class="d-inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="closed">
                                            <button class="btn btn-sm btn-outline-secondary">
                                                <i class="fa-solid fa-folder-closed me-1"></i>Close
                                            </button>
                                        </form>
                                        <a class="btn btn-sm btn-outline-warning" href="{{ route('student.matches') }}">
                                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Matches
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ $role === 'student' ? route('student.lost-items.destroy', $item) : route('admin.lost-items.destroy', $item) }}" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-confirm-submit data-confirm-title="Delete lost report" data-confirm-message="Move this lost report to deleted records?" data-confirm-button="Delete">
                                            <i class="fa-solid fa-trash-can me-1"></i>Delete
                                        </button>
                                    </form>
                                    @if($role === 'student')</div>@endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $role === 'admin' ? 7 : ($role === 'student' ? 5 : 6) }}">
                                <div class="lost-empty">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                    <p>No lost reports found.</p>
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
