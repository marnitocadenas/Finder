@extends('layouts.app')
@section('title', in_array($role, ['student', 'staff']) && isset($claimRoute) ? 'My Claims' : 'Claims Management')
@section('content')
<div class="claims-module">
    <div class="claims-hero">
        <div>
            <span class="module-eyebrow">{{ $role === 'student' ? 'Claim tracking' : ($role === 'staff' && isset($claimRoute) ? 'My claims' : ($role === 'staff' ? 'Review queue' : 'Admin review')) }}</span>
            <h1>{{ in_array($role, ['student', 'staff']) && isset($claimRoute) ? 'My Claims' : 'Claims' }}</h1>
            <p>{{ in_array($role, ['student', 'staff']) && isset($claimRoute) ? 'Track claim requests and review decisions for found items you submitted.' : 'Review ownership requests, inspect proof details, and keep item claim outcomes updated.' }}</p>
        </div>
        @if(in_array($role, ['student', 'staff']) && isset($claimRoute))
            <a href="{{ route($claimRoute.'.create') }}" class="btn btn-warning">
                <i class="fa-solid fa-plus me-1"></i>File a Claim
            </a>
        @elseif($role === 'admin')
            <a href="{{ route('admin.reports') }}" class="btn btn-warning">
                <i class="fa-solid fa-chart-column me-1"></i>Open Reports
            </a>
        @endif
    </div>

    @if($role === 'admin')
        <div class="claim-stat-grid">
            @foreach($claimStats ?? [] as $stat)
                <div class="claim-stat-card claim-stat-{{ $stat['tone'] }}">
                    <span><i class="fa-solid {{ $stat['icon'] }}"></i></span>
                    <div>
                        <small>{{ $stat['label'] }}</small>
                        <strong>{{ $stat['value'] }}</strong>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <section class="claims-panel">
        <div class="claims-panel-header">
            <div>
                <span class="module-eyebrow">Claim directory</span>
                <h2>Records</h2>
            </div>
            <div class="claim-tabs">
                <a class="{{ request('status') ? '' : 'active' }}" href="{{ url()->current() }}">All</a>
                @foreach(['pending','approved','rejected'] as $status)
                    <a class="{{ request('status') === $status ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['status' => $status]) }}">{{ Illuminate\Support\Str::title($status) }}</a>
                @endforeach
            </div>
        </div>

        @include('partials.filters', ['statuses' => ['pending','approved','rejected']])

        @if($role === 'admin')
            <form id="claims-bulk-form" class="bulk-toolbar" method="POST" action="{{ route('admin.claims.bulk') }}">
                @csrf
                <select name="action" class="form-select form-select-sm" required>
                    <option value="">Bulk action</option>
                    <option value="approved">Approve selected pending</option>
                    <option value="rejected">Reject selected pending</option>
                </select>
                <input class="form-control form-control-sm" name="review_note" placeholder="Optional review note">
                <button class="btn btn-sm btn-primary" type="submit"><i class="fa-solid fa-bolt me-1"></i>Apply</button>
            </form>
        @endif

        <div class="claims-table-wrap" id="claim-records-table">
            <table class="table claims-table excel-claims-table {{ $role === 'student' ? 'student-claims-table' : '' }} {{ $role === 'admin' ? 'admin-claims-table' : ($role === 'staff' ? 'staff-claims-table' : '') }} align-middle">
                @if($role === 'admin')
                    <colgroup>
                        <col class="admin-claims-col-select">
                        <col class="admin-claims-col-claim">
                        <col class="admin-claims-col-student">
                        <col class="admin-claims-col-found">
                        <col class="admin-claims-col-status">
                        <col class="admin-claims-col-reviewed">
                        <col class="admin-claims-col-action">
                    </colgroup>
                @endif
                <thead>
                    <tr>
                        @if($role === 'admin')<th><input type="checkbox" data-check-all></th>@endif
                        <th>Claim</th>
                        @if($role !== 'student' && !(isset($claimRoute) && $role === 'staff'))<th>Student</th>@endif
                        <th>Found Item</th>
                        <th>Status</th>
                        <th>Reviewed</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($claims as $claim)
                        <tr>
                            @if($role === 'admin')<td data-label="Select"><input type="checkbox" name="ids[]" value="{{ $claim->id }}" form="claims-bulk-form" @disabled($claim->status !== 'pending')></td>@endif
                            <td data-label="Claim">
                                <div class="claim-id">
                                    <span>#{{ $claim->id }}</span>
                                    <small>{{ $claim->created_at->format('M d, Y') }}</small>
                                </div>
                            </td>
                            @if($role !== 'student' && !(isset($claimRoute) && $role === 'staff'))
                                <td data-label="Student">
                                    <div class="claim-person">
                                        <span>{{ strtoupper(substr($claim->student->name ?? auth()->user()->name, 0, 1)) }}</span>
                                        <div>
                                            <strong title="{{ $claim->student->name ?? auth()->user()->name }}">{{ $claim->student->name ?? auth()->user()->name }}</strong>
                                            <small title="{{ $claim->student->email ?? auth()->user()->email }}">{{ $claim->student->email ?? auth()->user()->email }}</small>
                                        </div>
                                    </div>
                                </td>
                            @endif
                            <td data-label="Found Item">
                                <div class="claim-item">
                                    <i class="fa-solid {{ $claim->foundItem->category->icon ?? 'fa-box-open' }}"></i>
                                    <div>
                                        <strong title="{{ $claim->foundItem->title ?? '-' }}">{{ $claim->foundItem->title ?? '-' }}</strong>
                                        <small>{{ $claim->foundItem->category->name ?? 'Uncategorized' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Status"><x-status :status="$claim->status" /></td>
                            <td data-label="Reviewed" title="{{ optional($claim->reviewed_at)->format('M d, Y') ?? '-' }}">{{ optional($claim->reviewed_at)->format('M d, Y') ?? '-' }}</td>
                            <td data-label="Action" class="text-end">
                                @php
                                    $showRoute = isset($claimRoute)
                                        ? route($claimRoute.'.show', $claim)
                                        : ($role === 'staff' ? route('staff.claims.show', $claim) : ($role === 'admin' ? route('admin.claims.show', $claim) : route('student.claims.show', $claim)));
                                @endphp
                                <a class="btn btn-sm btn-outline-primary" href="{{ $showRoute }}">
                                    <i class="fa-solid fa-eye me-1"></i>Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $role === 'admin' ? 7 : (in_array($role, ['student', 'staff']) && isset($claimRoute) ? 5 : 6) }}">
                                <div class="claims-empty">
                                    <i class="fa-solid fa-file-circle-question"></i>
                                    <p>No claims found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $claims->links() }}
        </div>
    </section>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var params = new URLSearchParams(window.location.search);
        var hasFilter = ['q','category_id','status','from','to'].some(function (k) {
            return params.has(k) && params.get(k) !== '';
        });
        if (hasFilter) {
            var target = document.getElementById('claim-records-table');
            if (target) {
                setTimeout(function () {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 150);
            }
        }
    });
</script>
@endsection
