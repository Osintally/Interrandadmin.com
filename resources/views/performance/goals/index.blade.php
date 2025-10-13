@extends('layouts.pms-app')

@section('title', 'Goals & KPIs')

@section('page-title', 'Goals & KPIs')
@section('page-subtitle', 'Set and track your performance goals and key performance indicators')

@section('content')
<div class="page-actions">
    <a href="{{ route('performance.goals.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Set New Goal
    </a>
</div>

<!-- Goals Summary -->
<div class="dashboard-grid stats">
    @php
        $totalGoals = count($goals ?? []);
        $completedGoals = collect($goals ?? [])->where('status', 'achieved')->count();
        $inProgressGoals = collect($goals ?? [])->where('status', 'in_progress')->count();
        $averageProgress = $totalGoals > 0 ? collect($goals ?? [])->avg('progress') : 0;
    @endphp
    
    <div class="stat-card">
        <span class="stat-number">{{ $totalGoals }}</span>
        <span class="stat-label">Total Goals</span>
        <div class="stat-change positive">
            <i class="fas fa-bullseye"></i>
            Active goals
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">{{ $completedGoals }}</span>
        <span class="stat-label">Achieved</span>
        <div class="stat-change positive">
            <i class="fas fa-trophy"></i>
            Completed
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">{{ $inProgressGoals }}</span>
        <span class="stat-label">In Progress</span>
        <div class="stat-change warning">
            <i class="fas fa-hourglass-half"></i>
            Active
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">{{ number_format($averageProgress, 1) }}%</span>
        <span class="stat-label">Average Progress</span>
        <div class="stat-change positive">
            <i class="fas fa-chart-line"></i>
            Overall
        </div>
    </div>
</div>

<!-- Goals Grid -->
<div class="goals-container">
    @if(!empty($goals) && count($goals) > 0)
        <div class="goals-grid">
            @foreach($goals as $goal)
            <div class="goal-card {{ $goal['status'] }}">
                <div class="goal-header">
                    <h3 class="goal-title">{{ $goal['title'] }}</h3>
                    <span class="goal-status-badge {{ $goal['status'] }}">
                        @if($goal['status'] == 'achieved')
                            <i class="fas fa-trophy"></i> Achieved
                        @elseif($goal['status'] == 'in_progress')
                            <i class="fas fa-hourglass-half"></i> In Progress
                        @else
                            <i class="fas fa-clock"></i> {{ ucfirst($goal['status']) }}
                        @endif
                    </span>
                </div>
                
                <div class="goal-description">
                    {{ $goal['description'] }}
                </div>
                
                <div class="goal-metrics">
                    <div class="metric">
                        <label>Target:</label>
                        <span class="metric-value">{{ $goal['target_value'] }}{{ $goal['unit'] }}</span>
                    </div>
                    <div class="metric">
                        <label>Current:</label>
                        <span class="metric-value current">{{ $goal['current_value'] }}{{ $goal['unit'] }}</span>
                    </div>
                </div>
                
                <div class="goal-progress">
                    <div class="progress-header">
                        <span class="progress-label">Progress</span>
                        <span class="progress-percentage">{{ $goal['progress'] }}%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $goal['progress'] }}%"></div>
                    </div>
                </div>
                
                <div class="goal-footer">
                    <div class="goal-due-date">
                        <i class="fas fa-calendar"></i>
                        Due: {{ \Carbon\Carbon::parse($goal['due_date'])->format('M d, Y') }}
                    </div>
                    <div class="goal-actions">
                        <a href="{{ route('performance.goals.show', $goal['id']) }}" class="btn btn-outline btn-sm">
                            <i class="fas fa-eye"></i>
                            View
                        </a>
                        @if($goal['status'] == 'in_progress')
                        <button class="btn btn-primary btn-sm" onclick="updateProgress({{ $goal['id'] }}, {{ $goal['progress'] }})">
                            <i class="fas fa-edit"></i>
                            Update
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-bullseye"></i>
            </div>
            <div class="empty-state-title">No Goals Set Yet</div>
            <div class="empty-state-description">
                Start setting your performance goals to track your progress and achieve your objectives.
            </div>
            <div class="empty-state-actions">
                <a href="{{ route('performance.goals.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Set Your First Goal
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Update Progress Modal -->
<div class="modal-overlay" id="updateProgressModal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Update Goal Progress</h3>
            <button class="modal-close" onclick="closeUpdateModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="updateProgressForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Progress Percentage</label>
                    <input type="range" id="progressSlider" name="progress" min="0" max="100" value="0" class="progress-slider">
                    <div class="progress-display">
                        <span id="progressValue">0</span>%
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Add any notes about your progress..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeUpdateModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="submitProgressUpdate()">Update Progress</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.goals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
}

.goal-card {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
    border: 1px solid var(--border-color);
    padding: var(--spacing-lg);
    transition: var(--transition);
}

.goal-card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.goal-card.achieved {
    border-left: 4px solid var(--success-color);
}

.goal-card.in_progress {
    border-left: 4px solid var(--primary-color);
}

.goal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--spacing-md);
}

.goal-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
    flex: 1;
    margin-right: var(--spacing-sm);
}

.goal-status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: var(--border-radius);
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.goal-status-badge.achieved {
    background-color: var(--success-color);
    color: var(--white);
}

.goal-status-badge.in_progress {
    background-color: var(--primary-color);
    color: var(--white);
}

.goal-description {
    color: var(--text-secondary);
    margin-bottom: var(--spacing-lg);
    line-height: 1.5;
}

.goal-metrics {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
}

.metric {
    background: var(--bg-secondary);
    padding: var(--spacing-md);
    border-radius: var(--border-radius);
}

.metric label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-muted);
    margin-bottom: 0.25rem;
}

.metric-value {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--text-primary);
}

.metric-value.current {
    color: var(--primary-color);
}

.goal-progress {
    margin-bottom: var(--spacing-lg);
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-sm);
}

.progress-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
}

.progress-percentage {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--primary-color);
}

.progress-bar {
    height: 8px;
    background-color: var(--bg-secondary);
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
    border-radius: 4px;
    transition: width 0.3s ease;
}

.goal-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: var(--spacing-md);
    border-top: 1px solid var(--border-color);
}

.goal-due-date {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.goal-actions {
    display: flex;
    gap: 0.25rem;
}

.progress-slider {
    width: 100%;
    margin: var(--spacing-md) 0;
}

.progress-display {
    text-align: center;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color);
    margin: var(--spacing-md) 0;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
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

@media (max-width: 768px) {
    .goals-grid {
        grid-template-columns: 1fr;
    }
    
    .goal-header {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-sm);
    }
    
    .goal-metrics {
        grid-template-columns: 1fr;
    }
    
    .goal-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-sm);
    }
}
</style>
@endpush

@push('scripts')
<script>
let currentGoalId = null;

function updateProgress(goalId, currentProgress) {
    currentGoalId = goalId;
    const modal = document.getElementById('updateProgressModal');
    const slider = document.getElementById('progressSlider');
    const progressValue = document.getElementById('progressValue');
    
    slider.value = currentProgress;
    progressValue.textContent = currentProgress;
    
    modal.style.display = 'flex';
    modal.classList.add('show');
}

function closeUpdateModal() {
    const modal = document.getElementById('updateProgressModal');
    modal.style.display = 'none';
    modal.classList.remove('show');
    currentGoalId = null;
}

function submitProgressUpdate() {
    if (!currentGoalId) return;
    
    const form = document.getElementById('updateProgressForm');
    const formData = new FormData(form);
    
    // Here you would normally send the data to the server
    // For now, we'll just show a success message and reload
    
    if (window.PMS && window.PMS.notifications) {
        window.PMS.notifications.success('Goal progress updated successfully!');
    }
    
    closeUpdateModal();
    
    // In a real implementation, you would make an API call here
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

// Update progress display when slider changes
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('progressSlider');
    const progressValue = document.getElementById('progressValue');
    
    if (slider && progressValue) {
        slider.addEventListener('input', function() {
            progressValue.textContent = this.value;
        });
    }
});
</script>
@endpush
