@extends('layouts.app')
@section('title', $role === 'student' ? 'My Claims' : 'Claims Management')
@section('content')
<div class="claims-module">
    <div class="claims-hero">
        <div>
            <span class="module-eyebrow">{{ $role === 'student' ? 'Claim tracking' : ($role === 'staff' ? 'Review queue' : 'Admin review') }}</span>
            <h1>{{ $role === 'student' ? 'My Claims' : 'Claims' }}</h1>
            <p>{{ $role === 'student' ? 'Track claim requests and review decisions for found items you submitted.' : 'Review ownership requests, inspect proof details, and keep item claim outcomes updated.' }}</p>
        </div>
        @if($role === 'student')
            <a href="{{ route('student.claims.create') }}" class="btn btn-warning">
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

        <div class="claims-table-wrap">
            <table class="table claims-table excel-claims-table {{ $role === 'student' ? 'student-claims-table' : '' }} {{ $role === 'admin' ? 'admin-claims-table' : ($role === 'staff' ? 'staff-claims-table' : '') }} align-middle">
                <thead>
                    <tr>
                        @if($role === 'admin')<th><input type="checkbox" data-check-all></th>@endif
                        <th>Claim</th>
                        @if($role !== 'student')<th>Student</th>@endif
                        <th>Found Item</th>
                        <th>Status</th>
                        <th>Reviewed</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($claims as $claim)
                        <tr>
                            @if($role === 'admin')<td><input type="checkbox" name="ids[]" value="{{ $claim->id }}" form="claims-bulk-form" @disabled($claim->status !== 'pending')></td>@endif
                            <td>
                                <div class="claim-id">
                                    <span>#{{ $claim->id }}</span>
                                    <small>{{ $claim->created_at->format('M d, Y') }}</small>
                                </div>
                            </td>
                            @if($role !== 'student')
                                <td>
                                    <div class="claim-person">
                                        <span>{{ strtoupper(substr($claim->student->name ?? auth()->user()->name, 0, 1)) }}</span>
                                        <div>
                                            <strong>{{ $claim->student->name ?? auth()->user()->name }}</strong>
                                            <small>{{ $claim->student->email ?? auth()->user()->email }}</small>
                                        </div>
                                    </div>
                                </td>
                            @endif
                            <td>
                                <div class="claim-item">
                                    <i class="fa-solid {{ $claim->foundItem->category->icon ?? 'fa-box-open' }}"></i>
                                    <div>
                                        <strong>{{ $claim->foundItem->title ?? '-' }}</strong>
                                        <small>{{ $claim->foundItem->category->name ?? 'Uncategorized' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><x-status :status="$claim->status" /></td>
                            <td>{{ optional($claim->reviewed_at)->format('M d, Y') ?? '-' }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ $role==='student' ? route('student.claims.show',$claim) : ($role==='staff' ? route('staff.claims.show',$claim) : route('admin.claims.show',$claim)) }}">
                                    <i class="fa-solid fa-eye me-1"></i>Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $role === 'admin' ? 7 : ($role === 'student' ? 5 : 6) }}">
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
@endsection
