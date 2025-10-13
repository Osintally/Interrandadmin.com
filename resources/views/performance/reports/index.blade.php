@extends('layouts.pms-app')

@section('title', 'My Reports')

@section('page-title', 'My Reports')
@section('page-subtitle', 'Manage your performance reports and reviews')

@section('content')
<div class="page-actions">
    <a href="{{ route('performance.reports.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Create New Report
    </a>
</div>

<!-- Reports Summary Cards -->
<div class="dashboard-grid stats">
    <div class="stat-card">
        <span class="stat-number">{{ count($reports ?? []) }}</span>
        <span class="stat-label">Total Reports</span>
        <div class="stat-change positive">
            <i class="fas fa-file-alt"></i>
            All time
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">{{ collect($reports ?? [])->where('status', 'completed')->count() }}</span>
        <span class="stat-label">Completed</span>
        <div class="stat-change positive">
            <i class="fas fa-check"></i>
            Submitted
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">{{ collect($reports ?? [])->where('status', 'pending')->count() }}</span>
        <span class="stat-label">Pending</span>
        <div class="stat-change negative">
            <i class="fas fa-clock"></i>
            Need attention
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">{{ collect($reports ?? [])->where('status', 'completed')->avg('score') ? number_format(collect($reports ?? [])->where('status', 'completed')->avg('score'), 1) : 'N/A' }}</span>
        <span class="stat-label">Average Score</span>
        <div class="stat-change positive">
            <i class="fas fa-chart-line"></i>
            Overall
        </div>
    </div>
</div>

<!-- Reports Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Reports List</h3>
        <div class="card-actions">
            <button class="btn btn-outline btn-sm" onclick="refreshReports()">
                <i class="fas fa-sync-alt"></i>
                Refresh
            </button>
        </div>
    </div>
    
    <div class="card-body">
        @if(!empty($reports) && count($reports) > 0)
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Report Title</th>
                        <th>Template</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th>Due Date</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td>
                            <div class="report-title">{{ $report['title'] }}</div>
                        </td>
                        <td>{{ $report['template'] }}</td>
                        <td>
                            <span class="badge badge-{{ $report['status'] == 'completed' ? 'success' : ($report['status'] == 'pending' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($report['status']) }}
                            </span>
                        </td>
                        <td>
                            @if($report['score'])
                                <span class="score-display">{{ number_format($report['score'], 1) }}%</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($report['due_date'])->format('M d, Y') }}</td>
                        <td>
                            @if($report['submitted_at'])
                                {{ \Carbon\Carbon::parse($report['submitted_at'])->format('M d, Y') }}
                            @else
                                <span class="text-muted">Not submitted</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('performance.reports.show', $report['id']) }}" class="btn btn-outline btn-sm" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($report['status'] == 'pending')
                                <a href="{{ route('performance.reports.edit', $report['id']) }}" class="btn btn-primary btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="empty-state-title">No Reports Yet</div>
            <div class="empty-state-description">
                You haven't created any performance reports yet. Get started by creating your first report.
            </div>
            <div class="empty-state-actions">
                <a href="{{ route('performance.reports.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Create Your First Report
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-success {
    background-color: var(--success-color);
    color: var(--white);
}

.badge-warning {
    background-color: var(--warning-color);
    color: var(--white);
}

.badge-secondary {
    background-color: var(--text-secondary);
    color: var(--white);
}

.score-display {
    font-weight: 600;
    color: var(--primary-color);
}

.action-buttons {
    display: flex;
    gap: 0.25rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
}

.empty-state-icon {
    font-size: 3rem;
    color: var(--text-muted);
    margin-bottom: 1rem;
}

.empty-state-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.empty-state-description {
    color: var(--text-secondary);
    margin-bottom: 1.5rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.empty-state-actions {
    margin-top: 1.5rem;
}
</style>
@endpush

@push('scripts')
<script>
function refreshReports() {
    if (window.PMS && window.PMS.notifications) {
        window.PMS.notifications.info('Refreshing reports...');
        setTimeout(() => {
            window.location.reload();
        }, 500);
    } else {
        window.location.reload();
    }
}
</script>
@endpush
