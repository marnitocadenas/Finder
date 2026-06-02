@extends('layouts.app')
@section('title','Reports')
@section('content')
<div class="reports-module">
    <div class="reports-hero">
        <div>
            <span class="module-eyebrow">Analytics</span>
            <h1>Reports Module</h1>
            <p>Review inventory movement, claim decisions, and monthly activity across the campus lost and found workflow.</p>
        </div>
        <div class="reports-hero-actions">
            <span class="reports-updated">
                <i class="fa-solid fa-rotate"></i>Updated {{ now()->format('M d, Y h:i A') }}
            </span>
            <button type="button" class="btn btn-light" onclick="window.print()">
                <i class="fa-solid fa-print me-1"></i>Print
            </button>
            <a href="{{ route('admin.reports.export') }}" class="btn btn-warning">
                <i class="fa-solid fa-file-csv me-1"></i>Export CSV
            </a>
        </div>
    </div>

    <div class="report-stat-grid">
        @foreach($reportStats as $stat)
            <div class="report-metric-card report-metric-{{ $stat['tone'] }}">
                <span><i class="fa-solid {{ $stat['icon'] }}"></i></span>
                <div>
                    <small>{{ $stat['label'] }}</small>
                    <strong>{{ $stat['value'] }}</strong>
                    <em>{{ $stat['helper'] }}</em>
                </div>
            </div>
        @endforeach
    </div>

    <div class="report-summary-grid">
        <section class="report-summary-card">
            <div class="report-summary-icon report-summary-primary">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <div>
                <small>Lost Records</small>
                <strong>{{ $totalLostItems }}</strong>
                <span>{{ $openLostItems }} still marked lost</span>
            </div>
        </section>
        <section class="report-summary-card">
            <div class="report-summary-icon report-summary-success">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <div>
                <small>Found Inventory</small>
                <strong>{{ $totalFoundItems }}</strong>
                <span>{{ $unclaimedFoundItems }} unclaimed items</span>
            </div>
        </section>
        <section class="report-summary-card">
            <div class="report-summary-icon report-summary-warning">
                <i class="fa-solid fa-clipboard-check"></i>
            </div>
            <div>
                <small>Reviewed Claims</small>
                <strong>{{ $reviewedClaims }}</strong>
                <span>{{ $approvedClaims }} approved, {{ $rejectedClaims }} rejected</span>
            </div>
        </section>
    </div>

    <div class="reports-layout">
        <section class="report-panel report-panel-wide">
            <div class="report-panel-header">
                <div>
                    <span class="module-eyebrow">Inventory</span>
                    <h2>Items by Category</h2>
                    <p>Combined lost reports and found item posts by category.</p>
                </div>
                <span class="report-chip">{{ $totalItems }} items</span>
            </div>
            <div class="report-chart-box report-chart-box-lg">
                <canvas id="categoryChart"></canvas>
            </div>
        </section>

        <section class="report-panel">
            <div class="report-panel-header">
                <div>
                    <span class="module-eyebrow">Claims</span>
                    <h2>Claim Outcomes</h2>
                    <p>Approved, pending, and rejected ownership requests.</p>
                </div>
                <span class="report-chip">{{ $totalClaims }} claims</span>
            </div>
            <div class="report-donut-wrap">
                <div class="report-chart-box report-chart-box-md">
                    <canvas id="outcomeChart"></canvas>
                </div>
                <div class="report-outcome-list">
                    @foreach(['approved' => 'success', 'pending' => 'warning', 'rejected' => 'danger'] as $status => $tone)
                        <div>
                            <span class="report-dot report-dot-{{ $tone }}"></span>
                            <strong>{{ Illuminate\Support\Str::title($status) }}</strong>
                            <em>{{ $outcomes[$status] ?? 0 }}</em>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="report-panel report-panel-full">
            <div class="report-panel-header">
                <div>
                    <span class="module-eyebrow">Trend</span>
                    <h2>Claims per Month</h2>
                    <p>Monthly submission volume for claim requests.</p>
                </div>
                <span class="report-chip">Monthly</span>
            </div>
            <div class="report-chart-box report-chart-box-wide">
                <canvas id="claimChart"></canvas>
            </div>
        </section>

        <section class="report-panel report-panel-accent">
            <div class="report-panel-header">
                <div>
                    <span class="module-eyebrow">Focus</span>
                    <h2>Operational Snapshot</h2>
                    <p>Quick counts for the records that usually need follow-up.</p>
                </div>
            </div>
            <div class="report-focus-list">
                <a href="{{ route('admin.lost-items.index', ['status' => 'lost']) }}">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Open lost reports</span>
                    <strong>{{ $openLostItems }}</strong>
                </a>
                <a href="{{ route('admin.found-items.index', ['status' => 'unclaimed']) }}">
                    <i class="fa-solid fa-box-open"></i>
                    <span>Unclaimed found items</span>
                    <strong>{{ $unclaimedFoundItems }}</strong>
                </a>
                <a href="{{ route('admin.claims.index', ['status' => 'pending']) }}">
                    <i class="fa-solid fa-hourglass-half"></i>
                    <span>Pending claim reviews</span>
                    <strong>{{ $pendingClaims }}</strong>
                </a>
            </div>
        </section>
    </div>
</div>

@push('scripts')
<script>
const chartText = '#1C2333';
const chartMuted = '#6B7694';
const gridColor = 'rgba(107, 118, 148, 0.16)';
const emptyLabel = ['No data yet'];
Chart.defaults.font.family = "'DM Sans', sans-serif";
Chart.defaults.color = chartMuted;

const commonPlugins = {
    legend: { labels: { boxWidth: 12, boxHeight: 12, usePointStyle: true, padding: 18 } },
    tooltip: { backgroundColor: '#1C2333', titleColor: '#fff', bodyColor: '#fff', padding: 12, cornerRadius: 8 }
};

const categoryLabels = @json($categoryLabels);
const categoryCounts = @json($categoryCounts);
const outcomeLabels = @json($outcomes->keys());
const outcomeCounts = @json($outcomes->values());
const monthLabels = @json($claimsByMonth->keys());
const monthCounts = @json($claimsByMonth->values());

new Chart(document.getElementById('categoryChart'), {
    type: 'bar',
    data: {
        labels: categoryLabels.length ? categoryLabels : emptyLabel,
        datasets: [{
            label: 'Items',
            data: categoryCounts.length ? categoryCounts : [0],
            backgroundColor: ['#1A3C6E','#F4A800','#2D9E6B','#4E6E9E','#8FA7C7','#D94040','#6B7694'],
            borderRadius: 8,
            maxBarThickness: 54
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: commonPlugins,
        scales: {
            x: { grid: { display: false }, ticks: { color: chartText } },
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor } }
        }
    }
});

new Chart(document.getElementById('outcomeChart'), {
    type: 'doughnut',
    data: {
        labels: outcomeLabels.length ? outcomeLabels : emptyLabel,
        datasets: [{
            data: outcomeCounts.length ? outcomeCounts : [1],
            backgroundColor: outcomeCounts.length ? ['#2D9E6B','#F4A800','#D94040'] : ['#DDE3F0'],
            borderColor: '#FFFFFF',
            borderWidth: 4,
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: commonPlugins
    }
});

new Chart(document.getElementById('claimChart'), {
    type: 'line',
    data: {
        labels: monthLabels.length ? monthLabels : emptyLabel,
        datasets: [{
            label: 'Claims',
            data: monthCounts.length ? monthCounts : [0],
            borderColor: '#F4A800',
            backgroundColor: 'rgba(244,168,0,0.18)',
            pointBackgroundColor: '#1A3C6E',
            pointBorderColor: '#FFFFFF',
            pointBorderWidth: 3,
            pointRadius: 5,
            borderWidth: 3,
            fill: true,
            tension: 0.36
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: commonPlugins,
        scales: {
            x: { grid: { display: false }, ticks: { color: chartText } },
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor } }
        }
    }
});
</script>
@endpush
@endsection
