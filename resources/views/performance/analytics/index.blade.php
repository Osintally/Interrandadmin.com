@extends('layouts.pms-app')

@section('title', 'Analytics Dashboard')

@section('page-title', 'Performance Analytics')
@section('page-subtitle', 'Comprehensive insights into organizational performance metrics')

@section('content')
<div class="page-actions">
    <button class="btn btn-outline" onclick="exportAnalytics()">
        <i class="fas fa-download"></i>
        Export Report
    </button>
    <button class="btn btn-primary" onclick="refreshAnalytics()">
        <i class="fas fa-sync-alt"></i>
        Refresh Data
    </button>
</div>

<!-- Analytics Overview -->
<div class="dashboard-grid stats">
    @php $overview = $analytics['overview'] ?? [] @endphp
    <div class="stat-card">
        <span class="stat-number">{{ $overview['total_reviews'] ?? 0 }}</span>
        <span class="stat-label">Total Reviews</span>
        <div class="stat-change positive">
            <i class="fas fa-file-alt"></i>
            All time
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">{{ number_format($overview['average_score'] ?? 0, 1) }}%</span>
        <span class="stat-label">Average Score</span>
        <div class="stat-change positive">
            <i class="fas fa-chart-line"></i>
            Organization-wide
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">{{ number_format($overview['completion_rate'] ?? 0, 1) }}%</span>
        <span class="stat-label">Completion Rate</span>
        <div class="stat-change positive">
            <i class="fas fa-check-circle"></i>
            This period
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">+{{ number_format($overview['improvement_rate'] ?? 0, 1) }}%</span>
        <span class="stat-label">Improvement Rate</span>
        <div class="stat-change positive">
            <i class="fas fa-trending-up"></i>
            Year over year
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="dashboard-grid layout">
    <!-- Performance Trends Chart -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Performance Trends</h3>
            <div class="card-actions">
                <select class="form-control form-control-sm" id="trendsTimeframe">
                    <option value="6months">Last 6 Months</option>
                    <option value="year">Last Year</option>
                    <option value="2years">Last 2 Years</option>
                </select>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="trendsChart"></canvas>
        </div>
    </div>
    
    <!-- Department Performance -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Department Performance</h3>
        </div>
        <div class="card-body">
            <div class="department-list">
                @foreach($analytics['department_performance'] ?? [] as $dept)
                <div class="department-item">
                    <div class="department-info">
                        <h4 class="department-name">{{ $dept['name'] }}</h4>
                        <div class="department-score">{{ number_format($dept['score'], 1) }}%</div>
                    </div>
                    <div class="department-trend {{ $dept['trend'] }}">
                        @if($dept['trend'] == 'up')
                            <i class="fas fa-arrow-up"></i>
                        @elseif($dept['trend'] == 'down')
                            <i class="fas fa-arrow-down"></i>
                        @else
                            <i class="fas fa-minus"></i>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Detailed Analytics Tables -->
<div class="dashboard-grid layout">
    <!-- Top Performers -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Top Performers</h3>
            <a href="{{ route('performance.leaderboards.index') }}" class="btn btn-outline btn-sm">
                View All
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="performer-list">
                <div class="performer-item">
                    <div class="performer-rank">1</div>
                    <div class="performer-info">
                        <div class="performer-name">Sarah Johnson</div>
                        <div class="performer-dept">Sales Department</div>
                    </div>
                    <div class="performer-score">98.5%</div>
                </div>
                
                <div class="performer-item">
                    <div class="performer-rank">2</div>
                    <div class="performer-info">
                        <div class="performer-name">Michael Chen</div>
                        <div class="performer-dept">Engineering</div>
                    </div>
                    <div class="performer-score">96.2%</div>
                </div>
                
                <div class="performer-item">
                    <div class="performer-rank">3</div>
                    <div class="performer-info">
                        <div class="performer-name">Emily Rodriguez</div>
                        <div class="performer-dept">Marketing</div>
                    </div>
                    <div class="performer-score">94.8%</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Performance Distribution -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Score Distribution</h3>
        </div>
        <div class="chart-container">
            <canvas id="distributionChart"></canvas>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.department-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.department-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--spacing-md);
    background: var(--bg-secondary);
    border-radius: var(--border-radius);
}

.department-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    flex: 1;
}

.department-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.department-score {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--primary-color);
}

.department-trend {
    padding: 0.25rem;
    border-radius: 50%;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
}

.department-trend.up {
    background: var(--success-color);
    color: var(--white);
}

.department-trend.down {
    background: var(--danger-color);
    color: var(--white);
}

.department-trend.stable {
    background: var(--text-muted);
    color: var(--white);
}

.performer-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.performer-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    background: var(--bg-secondary);
    border-radius: var(--border-radius);
}

.performer-rank {
    width: 2rem;
    height: 2rem;
    background: var(--primary-color);
    color: var(--white);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.875rem;
}

.performer-info {
    flex: 1;
}

.performer-name {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.125rem;
}

.performer-dept {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.performer-score {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--success-color);
}

@media (max-width: 768px) {
    .dashboard-grid.layout {
        grid-template-columns: 1fr;
    }
    
    .department-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeTrendsChart();
    initializeDistributionChart();
});

function initializeTrendsChart() {
    const ctx = document.getElementById('trendsChart');
    if (ctx && typeof Chart !== 'undefined') {
        const trendsData = {!! json_encode($analytics['trends'] ?? [
            'monthly_scores' => [85, 87, 89, 86, 90, 88], 
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']
        ]) !!};
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: trendsData.labels,
                datasets: [{
                    label: 'Average Performance Score',
                    data: trendsData.monthly_scores,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#4f46e5',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#4f46e5',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: false,
                        min: 70,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }
}

function initializeDistributionChart() {
    const ctx = document.getElementById('distributionChart');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Excellent (90-100%)', 'Good (80-89%)', 'Average (70-79%)', 'Below Average (<70%)'],
                datasets: [{
                    data: [25, 45, 20, 10],
                    backgroundColor: [
                        '#10b981',
                        '#4f46e5', 
                        '#f59e0b',
                        '#ef4444'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }
}

function refreshAnalytics() {
    if (window.PMS && window.PMS.notifications) {
        window.PMS.notifications.info('Refreshing analytics data...');
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    } else {
        window.location.reload();
    }
}

function exportAnalytics() {
    if (window.PMS && window.PMS.notifications) {
        window.PMS.notifications.success('Analytics report exported successfully!');
    }
    // In a real implementation, trigger download of analytics report
}

// Timeframe selector
document.getElementById('trendsTimeframe')?.addEventListener('change', function() {
    if (window.PMS && window.PMS.notifications) {
        window.PMS.notifications.info('Updating chart data...');
    }
    // In a real implementation, update chart with new timeframe data
});
</script>
@endpush
