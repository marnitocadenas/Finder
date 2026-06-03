@extends('layouts.app')
@section('title','Activity Logs')
@section('content')
<div class="logs-module">
    <div class="logs-hero">
        <div>
            <span class="module-eyebrow">Audit trail</span>
            <h1>Activity Logs</h1>
            <p>Review account actions, record changes, timestamps, and source IPs across the system.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-light">
            <i class="fa-solid fa-arrow-left me-1"></i>Overview
        </a>
    </div>

    <div class="log-stat-grid">
        @foreach($logStats as $stat)
            <div class="log-stat-card log-stat-{{ $stat['tone'] }}">
                <span><i class="fa-solid {{ $stat['icon'] }}"></i></span>
                <div>
                    <small>{{ $stat['label'] }}</small>
                    <strong>{{ $stat['value'] }}</strong>
                </div>
            </div>
        @endforeach
    </div>

    <section class="logs-panel">
        <div class="logs-panel-header">
            <div>
                <span class="module-eyebrow">Events</span>
                <h2>System Activity</h2>
            </div>
        </div>

        @include('partials.filters', ['statuses' => []])

        <div class="logs-table-wrap">
            <table class="table logs-table excel-logs-table align-middle">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Target</th>
                        <th>IP Address</th>
                        <th>Date</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                <div class="log-user">
                                    <span>{{ strtoupper(substr($log->user->name ?? 'System', 0, 1)) }}</span>
                                    <div>
                                        <strong>{{ $log->user->name ?? 'System' }}</strong>
                                        <small>{{ $log->user->email ?? 'Automated event' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="log-action">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                    <span>{{ $log->action }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="log-target">
                                    {{ class_basename($log->target_type) ?: 'Record' }}
                                    @if($log->target_id)
                                        <small>#{{ $log->target_id }}</small>
                                    @endif
                                </span>
                            </td>
                            <td><span class="log-ip">{{ $log->ip_address ?: '-' }}</span></td>
                            <td>
                                <strong>{{ $log->created_at->format('M d, Y') }}</strong>
                            </td>
                            <td>
                                <div class="log-time">
                                    <span>{{ $log->created_at->format('h:i A') }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="logs-empty">
                                    <i class="fa-solid fa-clock"></i>
                                    <p>No activity logs found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $logs->links() }}
        </div>
    </section>
</div>
@endsection
