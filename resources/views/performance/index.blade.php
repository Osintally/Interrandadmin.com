@extends('layouts.pms-app')

@section('title', 'My Performance')

@section('page-title', 'My Performance')
@section('page-subtitle', 'Track your performance metrics and progress')

@section('content')
<div class="page-actions">
    <a href="{{ route('performance.reports.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        New Report
    </a>
    <a href="{{ route('performance.goals.create') }}" class="btn btn-outline">
        <i class="fas fa-bullseye"></i>
        Set Goal
    </a>
</div>

<!-- Performance Summary Cards -->
<div class="dashboard-grid stats">
    <div class="stat-card highlight">
        <span class="stat-number">{{ number_format($performanceData['personal_performance']['current_score'], 1) }}%</span>
        <span class="stat-label">Current Score</span>
        <div class="stat-change {{ $performanceData['personal_performance']['improvement'] > 0 ? 'positive' : 'negative' }}">
            <i class="fas fa-{{ $performanceData['personal_performance']['improvement'] > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
            {{ $performanceData['personal_performance']['improvement'] > 0 ? '+' : '' }}{{ number_format($performanceData['personal_performance']['improvement'], 1) }}% from last review
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">{{ $performanceData['personal_performance']['goals_completed'] }}/{{ $performanceData['personal_performance']['total_goals'] }}</span>
        <span class="stat-label">Goals Completed</span>
        <div class="stat-change positive">
            <i class="fas fa-bullseye"></i>
            {{ number_format($performanceData['personal_performance']['completion_rate'], 1) }}% completion rate
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">#{{ $performanceData['department_average']['ranking'] }}</span>
        <span class="stat-label">Department Ranking</span>
        <div class="stat-change positive">
            <i class="fas fa-trophy"></i>
            Out of {{ $performanceData['department_average']['total_members'] }} members
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">{{ number_format($performanceData['department_average']['average_score'], 1) }}%</span>
        <span class="stat-label">Department Average</span>
        <div class="stat-change {{ $performanceData['personal_performance']['current_score'] > $performanceData['department_average']['average_score'] ? 'positive' : 'negative' }}">
            <i class="fas fa-chart-line"></i>
            {{ $performanceData['personal_performance']['current_score'] > $performanceData['department_average']['average_score'] ? 'Above' : 'Below' }} average
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="dashboard-grid layout">
    <!-- Performance Trends -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Performance Trends</h3>
            <div class="card-actions">
                <select class="form-control form-control-sm">
                    <option>Last 6 Months</option>
                    <option>Last Year</option>
                    <option>All Time</option>
                </select>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="performanceChart"></canvas>
        </div>
    </div>
    
    <!-- Recent Reviews -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Reviews</h3>
            <a href="{{ route('performance.reports.index') }}" class="btn btn-outline btn-sm">
                View All
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="review-list">
                @foreach($performanceData['recent_reviews'] as $review)
                <div class="review-item">
                    <div class="review-info">
                        <h4 class="review-title">{{ $review['title'] }}</h4>
                        <div class="review-meta">
                            <span class="review-date">{{ \Carbon\Carbon::parse($review['date'])->format('M d, Y') }}</span>
                            <span class="review-reviewer">by {{ $review['reviewer'] }}</span>
                        </div>
                    </div>
                    <div class="review-score">
                        <span class="score-value">{{ number_format($review['score'], 1) }}%</span>
                        <span class="score-status {{ $review['status'] }}">{{ ucfirst($review['status']) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Goals Progress & Upcoming Items -->
<div class="dashboard-grid layout">
    <!-- Goals Progress -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Goals Progress</h3>
            <a href="{{ route('performance.goals.index') }}" class="btn btn-outline btn-sm">
                Manage Goals
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="progress-overview">
                <div class="progress-circle">
                    <svg viewBox="0 0 36 36" class="circular-chart">
                        <path class="circle-bg"
                              d="M18 2.0845
                                a 15.9155 15.9155 0 0 1 0 31.831
                                a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path class="circle"
                              stroke-dasharray="{{ $performanceData['personal_performance']['completion_rate'] }}, 100"
                              d="M18 2.0845
                                a 15.9155 15.9155 0 0 1 0 31.831
                                a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <text x="18" y="20.35" class="percentage">{{ number_format($performanceData['personal_performance']['completion_rate'], 0) }}%</text>
                    </svg>
                </div>
                <div class="progress-details">
                    <h4>Goals Completed</h4>
                    <p>{{ $performanceData['personal_performance']['goals_completed'] }} out of {{ $performanceData['personal_performance']['total_goals'] }} goals achieved</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Upcoming Deadlines -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Upcoming Deadlines</h3>
        </div>
        <div class="card-body">
            <div class="deadline-list">
                @foreach($performanceData['upcoming_deadlines'] as $deadline)
                <div class="deadline-item">
                    <div class="deadline-priority {{ $deadline['priority'] }}">
                        <div class="priority-indicator"></div>
                    </div>
                    <div class="deadline-info">
                        <h4 class="deadline-title">{{ $deadline['title'] }}</h4>
                        <div class="deadline-meta">
                            <span class="deadline-date">Due: {{ \Carbon\Carbon::parse($deadline['due_date'])->format('M d, Y') }}</span>
                            <span class="deadline-remaining">{{ $deadline['days_remaining'] }} days remaining</span>
                        </div>
                    </div>
                    <div class="deadline-actions">
                        <button class="btn btn-outline btn-sm">
                            <i class="fas fa-play"></i>
                            Start
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.stat-card.highlight {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    color: var(--white);
}

.stat-card.highlight .stat-number,
.stat-card.highlight .stat-label {
    color: var(--white);
}

.review-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.review-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-md);
    background: var(--bg-secondary);
    border-radius: var(--border-radius);
}

.review-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 0.25rem 0;
}

.review-meta {
    display: flex;
    gap: var(--spacing-sm);
    font-size: 0.75rem;
    color: var(--text-muted);
}

.review-score {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.25rem;
}

.score-value {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--primary-color);
}

.score-status.completed {
    background: var(--success-color);
    color: var(--white);
    padding: 0.125rem 0.5rem;
    border-radius: var(--border-radius);
    font-size: 0.625rem;
    font-weight: 600;
}

.progress-overview {
    display: flex;
    align-items: center;
    gap: var(--spacing-lg);
}

.progress-circle {
    flex-shrink: 0;
}

.circular-chart {
    display: block;
    margin: 10px auto;
    max-width: 80px;
    max-height: 80px;
}

.circle-bg {
    fill: none;
    stroke: var(--border-color);
    stroke-width: 3.8;
}

.circle {
    fill: none;
    stroke: var(--primary-color);
    stroke-width: 2.8;
    stroke-linecap: round;
    animation: progress 1s ease-in-out forwards;
}

.percentage {
    fill: var(--text-primary);
    font-family: sans-serif;
    font-size: 0.5em;
    text-anchor: middle;
    font-weight: 600;
}

@keyframes progress {
    0% {
        stroke-dasharray: 0 100;
    }
}

.deadline-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.deadline-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    background: var(--bg-secondary);
    border-radius: var(--border-radius);
}

.deadline-priority {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
}

.deadline-priority.high {
    background: rgba(239, 68, 68, 0.1);
}

.deadline-priority.medium {
    background: rgba(245, 158, 11, 0.1);
}

.deadline-priority.low {
    background: rgba(16, 185, 129, 0.1);
}

.priority-indicator {
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
}

.deadline-priority.high .priority-indicator {
    background: var(--danger-color);
}

.deadline-priority.medium .priority-indicator {
    background: var(--warning-color);
}

.deadline-priority.low .priority-indicator {
    background: var(--success-color);
}

.deadline-info {
    flex: 1;
}

.deadline-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 0.25rem 0;
}

.deadline-meta {
    display: flex;
    gap: var(--spacing-md);
    font-size: 0.75rem;
    color: var(--text-muted);
}

.deadline-remaining {
    font-weight: 600;
    color: var(--warning-color);
}

@media (max-width: 768px) {
    .dashboard-grid.layout {
        grid-template-columns: 1fr;
    }
    
    .review-item {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-sm);
    }
    
    .progress-overview {
        flex-direction: column;
        text-align: center;
    }
    
    .deadline-item {
        flex-wrap: wrap;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializePerformanceChart();
});

function initializePerformanceChart() {
    const ctx = document.getElementById('performanceChart');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Performance Score',
                    data: [82, 84, 86, 83, 87, {{ $performanceData['personal_performance']['current_score'] }}],
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#4f46e5',
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
</script>
@endpush
