@extends('layouts.pms-app')

@section('title', 'Create New Goal')

@section('page-title', 'Create New Goal')
@section('page-subtitle', 'Set a new performance goal to track your progress')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Create New Performance Goal</h1>
        <p class="page-subtitle">Define your objectives and track your progress towards achieving them</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('performance.goals.index') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i>
            Back to Goals
        </a>
    </div>
</div>

<form action="{{ route('performance.goals.store') }}" method="POST" id="createGoalForm">
    @csrf
    
    <div class="dashboard-grid layout">
        <!-- Goal Details -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Goal Information</h3>
                <small class="card-subtitle">Basic details about your performance goal</small>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Goal Title <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="e.g., Increase Sales Revenue by 20%" value="{{ old('title') }}" required>
                    @error('title')
                        <small class="form-error">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Describe what you want to achieve and why it's important">{{ old('description') }}</textarea>
                    @error('description')
                        <small class="form-error">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Goal Category</label>
                    <select name="category" class="form-control">
                        <option value="">Select a category</option>
                        <option value="sales" {{ old('category') == 'sales' ? 'selected' : '' }}>Sales Performance</option>
                        <option value="productivity" {{ old('category') == 'productivity' ? 'selected' : '' }}>Productivity</option>
                        <option value="skills" {{ old('category') == 'skills' ? 'selected' : '' }}>Skill Development</option>
                        <option value="leadership" {{ old('category') == 'leadership' ? 'selected' : '' }}>Leadership</option>
                        <option value="customer" {{ old('category') == 'customer' ? 'selected' : '' }}>Customer Service</option>
                        <option value="quality" {{ old('category') == 'quality' ? 'selected' : '' }}>Quality Improvement</option>
                        <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Priority Level</label>
                    <div class="priority-options">
                        <label class="priority-option">
                            <input type="radio" name="priority" value="low" {{ old('priority') == 'low' ? 'checked' : '' }}>
                            <span class="priority-indicator low"></span>
                            <span class="priority-label">Low</span>
                        </label>
                        <label class="priority-option">
                            <input type="radio" name="priority" value="medium" {{ old('priority', 'medium') == 'medium' ? 'checked' : '' }}>
                            <span class="priority-indicator medium"></span>
                            <span class="priority-label">Medium</span>
                        </label>
                        <label class="priority-option">
                            <input type="radio" name="priority" value="high" {{ old('priority') == 'high' ? 'checked' : '' }}>
                            <span class="priority-indicator high"></span>
                            <span class="priority-label">High</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Goal Metrics -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Goal Metrics</h3>
                <small class="card-subtitle">Define measurable targets for your goal</small>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Target Value <span class="required">*</span></label>
                    <div class="target-input">
                        <input type="number" name="target_value" class="form-control" step="0.01" placeholder="100" value="{{ old('target_value') }}" required>
                        <input type="text" name="unit" class="form-control unit-input" placeholder="%" value="{{ old('unit') }}" maxlength="10">
                    </div>
                    <small class="form-help">Enter the target value and unit (e.g., 100 sales, 95%, 5000$)</small>
                    @error('target_value')
                        <small class="form-error">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Current Value</label>
                    <input type="number" name="current_value" class="form-control" step="0.01" placeholder="75" value="{{ old('current_value', 0) }}">
                    <small class="form-help">Your starting point or current progress</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Measurement Method</label>
                    <select name="measurement_method" class="form-control">
                        <option value="manual" {{ old('measurement_method', 'manual') == 'manual' ? 'selected' : '' }}>Manual Update</option>
                        <option value="automatic" {{ old('measurement_method') == 'automatic' ? 'selected' : '' }}>Automatic (System Calculated)</option>
                        <option value="manager" {{ old('measurement_method') == 'manager' ? 'selected' : '' }}>Manager Assessment</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Due Date <span class="required">*</span></label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}" min="{{ date('Y-m-d') }}" required>
                    @error('due_date')
                        <small class="form-error">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    
    <!-- Goal Planning -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Action Plan</h3>
            <small class="card-subtitle">Break down your goal into actionable steps</small>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Key Actions</label>
                <textarea name="action_plan" class="form-control" rows="4" placeholder="List the key actions you will take to achieve this goal...">{{ old('action_plan') }}</textarea>
                <small class="form-help">Describe the specific steps you'll take to reach your target</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Success Criteria</label>
                <textarea name="success_criteria" class="form-control" rows="3" placeholder="How will you know when you've succeeded?">{{ old('success_criteria') }}</textarea>
                <small class="form-help">Define what success looks like for this goal</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Potential Challenges</label>
                <textarea name="challenges" class="form-control" rows="3" placeholder="What obstacles might you face?">{{ old('challenges') }}</textarea>
                <small class="form-help">Identify potential roadblocks and how you'll address them</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Required Resources</label>
                <textarea name="resources" class="form-control" rows="2" placeholder="What support, tools, or resources do you need?">{{ old('resources') }}</textarea>
                <small class="form-help">List any support or resources needed to achieve this goal</small>
            </div>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="form-actions">
        <button type="button" class="btn btn-outline" onclick="window.history.back()">Cancel</button>
        <button type="submit" class="btn btn-success" id="createGoalBtn">
            <i class="fas fa-bullseye"></i>
            Create Goal
        </button>
    </div>
</form>
@endsection

@push('styles')
<style>
.required {
    color: var(--danger-color);
}

.form-error {
    color: var(--danger-color);
    font-size: 0.75rem;
    margin-top: 0.25rem;
    display: block;
}

.form-help {
    color: var(--text-muted);
    font-size: 0.75rem;
    margin-top: 0.25rem;
    display: block;
}

.target-input {
    display: flex;
    gap: var(--spacing-sm);
}

.unit-input {
    max-width: 100px;
    flex-shrink: 0;
}

.priority-options {
    display: flex;
    gap: var(--spacing-lg);
    margin-top: var(--spacing-sm);
}

.priority-option {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    cursor: pointer;
    padding: var(--spacing-sm);
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.priority-option:hover {
    background-color: var(--bg-secondary);
}

.priority-option input[type="radio"] {
    display: none;
}

.priority-indicator {
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    border: 2px solid transparent;
    transition: var(--transition);
}

.priority-indicator.low {
    background-color: var(--success-color);
}

.priority-indicator.medium {
    background-color: var(--warning-color);
}

.priority-indicator.high {
    background-color: var(--danger-color);
}

.priority-option input[type="radio"]:checked + .priority-indicator {
    border-color: var(--text-primary);
    box-shadow: 0 0 0 2px var(--white), 0 0 0 4px var(--primary-color);
}

.priority-label {
    font-weight: 500;
    color: var(--text-primary);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: var(--spacing-md);
    margin-top: var(--spacing-xl);
    padding-top: var(--spacing-lg);
    border-top: 1px solid var(--border-color);
}

@media (max-width: 768px) {
    .dashboard-grid.layout {
        grid-template-columns: 1fr;
    }
    
    .target-input {
        flex-direction: column;
    }
    
    .unit-input {
        max-width: none;
    }
    
    .priority-options {
        flex-direction: column;
        gap: var(--spacing-sm);
    }
    
    .form-actions {
        flex-direction: column-reverse;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createGoalForm');
    const submitBtn = document.getElementById('createGoalBtn');
    
    form.addEventListener('submit', function(e) {
        // Basic validation
        const title = form.querySelector('[name="title"]').value.trim();
        const targetValue = form.querySelector('[name="target_value"]').value;
        const dueDate = form.querySelector('[name="due_date"]').value;
        
        if (!title || !targetValue || !dueDate) {
            e.preventDefault();
            alert('Please fill in all required fields (marked with *)');
            return;
        }
        
        // Check if due date is in the future
        const selectedDate = new Date(dueDate);
        const today = new Date();
        if (selectedDate <= today) {
            e.preventDefault();
            alert('Due date must be in the future');
            return;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Goal...';
    });
    
    // Auto-calculate progress if current value changes
    const currentValueInput = form.querySelector('[name="current_value"]');
    const targetValueInput = form.querySelector('[name="target_value"]');
    
    function updateProgress() {
        const current = parseFloat(currentValueInput.value) || 0;
        const target = parseFloat(targetValueInput.value) || 1;
        
        if (target > 0) {
            const progress = Math.min(100, Math.max(0, (current / target) * 100));
            // You could show progress here if needed
        }
    }
    
    currentValueInput.addEventListener('input', updateProgress);
    targetValueInput.addEventListener('input', updateProgress);
});
</script>
@endpush
