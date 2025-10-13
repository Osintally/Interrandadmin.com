@extends('layouts.pms-app')

@section('title', 'Performance Dashboard')

@section('page-title', 'Performance Dashboard')
@section('page-subtitle', 'Real-time insights into organizational performance')

@section('content')
@php
    // Sample dashboard stats - replace with actual data from your controllers
    $stats = $stats ?? [
        'total_employees' => 150,
        'active_templates' => 12,
        'pending_reports' => 8,
        'completion_rate' => 85
    ];
@endphp

<!-- Dashboard Statistics -->
<div class="dashboard-grid stats">
    <div class="stat-card">
        <span class="stat-number">{{ number_format($stats['total_employees'] ?? 0) }}</span>
        <span class="stat-label">Total Employees</span>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            +5% from last month
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">{{ number_format($stats['active_templates'] ?? 0) }}</span>
        <span class="stat-label">Active Templates</span>
        <div class="stat-change neutral">
            <i class="fas fa-minus"></i>
            No change
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">{{ number_format($stats['pending_reports'] ?? 0) }}</span>
        <span class="stat-label">Pending Reports</span>
        <div class="stat-change negative">
            <i class="fas fa-clock"></i>
            Needs attention
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">{{ number_format($stats['completion_rate'] ?? 0) }}%</span>
        <span class="stat-label">Completion Rate</span>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            +2.1% increase
        </div>
    </div>
</div>

<!-- Charts and Leaderboard -->
<div class="dashboard-grid layout">
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Performance Trends</h3>
                <p class="card-subtitle">Performance metrics over the last 6 months</p>
            </div>
            <button class="btn btn-outline btn-sm" onclick="exportChart()">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
        <div class="chart-container">
            <canvas id="performanceChart"></canvas>
        </div>
    </div>
    
</div>

<!-- Recent Activities and Quick Actions -->
<div class="dashboard-grid layout">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Activities</h3>
        </div>
        <div class="card-body">
            <div class="activity-list">
                <div class="activity-item">
                    <div class="activity-icon bg-success">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Performance review completed</div>
                        <div class="activity-subtitle">John Doe - 2 hours ago</div>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon bg-info">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">New template created</div>
                        <div class="activity-subtitle">Q1 2024 Review Template - 5 hours ago</div>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon bg-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Report deadline approaching</div>
                        <div class="activity-subtitle">Monthly review due in 2 days</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="#" class="btn btn-outline btn-sm">View All Activities</a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Quick Actions</h3>
        </div>
        <div class="card-body">
            <div class="quick-actions">
                <a href="{{ route('performance.reports.create') }}" class="quick-action-item">
                    <div class="quick-action-icon bg-primary">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="quick-action-content">
                        <div class="quick-action-title">Create Report</div>
                        <div class="quick-action-subtitle">Start a new performance report</div>
                    </div>
                </a>
                
                <a href="{{ route('performance.goals.index') }}" class="quick-action-item">
                    <div class="quick-action-icon bg-success">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <div class="quick-action-content">
                        <div class="quick-action-title">Set Goals</div>
                        <div class="quick-action-subtitle">Define your performance goals</div>
                    </div>
                </a>
                
                <a href="{{ route('performance.templates.builder') }}" class="quick-action-item">
                    <div class="quick-action-icon bg-info">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="quick-action-content">
                        <div class="quick-action-title">Build Template</div>
                        <div class="quick-action-subtitle">Create custom templates</div>
                    </div>
                </a>
                
                <a href="{{ route('performance.analytics.index') }}" class="quick-action-item">
                    <div class="quick-action-icon bg-warning">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="quick-action-content">
                        <div class="quick-action-title">View Analytics</div>
                        <div class="quick-action-subtitle">Detailed performance insights</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.activity-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.activity-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-sm);
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.activity-item:hover {
    background-color: var(--bg-secondary);
}

.activity-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 0.875rem;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.875rem;
}

.activity-subtitle {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 0.125rem;
}

.quick-actions {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.quick-action-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    text-decoration: none;
    transition: var(--transition);
}

.quick-action-item:hover {
    border-color: var(--primary-color);
    box-shadow: var(--shadow);
    transform: translateY(-2px);
}

.quick-action-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 1rem;
}

.quick-action-content {
    flex: 1;
}

.quick-action-title {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.875rem;
}

.quick-action-subtitle {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 0.125rem;
}

@media (max-width: 768px) {
    .dashboard-grid.layout {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
    
    .activity-item,
    .quick-action-item {
        padding: var(--spacing-sm);
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize performance chart
    const ctx = document.getElementById('performanceChart');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Department Average',
                    data: [85, 87, 89, 86, 90, 88],
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#4f46e5',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }, {
                    label: 'Company Average',
                    data: [82, 84, 86, 83, 87, 85],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: false,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
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
                        },
                        ticks: {
                            color: '#6b7280'
                        }
                    },
                    y: {
                        beginAtZero: false,
                        min: 70,
                        max: 100,
                        grid: {
                            color: '#f3f4f6'
                        },
                        ticks: {
                            color: '#6b7280',
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                }
            }
        });
    }
    
    // Auto-refresh dashboard data
    if (window.PMS && window.PMS.api) {
        setInterval(async () => {
            try {
                await window.PMS.api.dashboard.getStats();
                // Optionally update the display without full page reload
            } catch (error) {
                console.warn('Failed to refresh dashboard data:', error);
            }
        }, 300000); // Refresh every 5 minutes
    }
});

function exportChart() {
    const canvas = document.getElementById('performanceChart');
    if (canvas) {
        const url = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.download = 'performance-trends.png';
        link.href = url;
        link.click();
        
        if (window.PMS && window.PMS.notifications) {
            window.PMS.notifications.success('Chart exported successfully');
        }
    }
}
</script>
@endpush
