@extends('layouts.pms-app')

@section('title', 'Edit Report')

@section('page-title', 'Edit Report')
@section('page-subtitle', 'Update your performance report')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Performance Report</h1>
        <p class="page-subtitle">Update the details and content of your performance report</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('performance.reports.show', $report['id'] ?? 1) }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i>
            Back to Report
        </a>
        <button class="btn btn-outline" onclick="saveDraft()">
            <i class="fas fa-save"></i>
            Save as Draft
        </button>
    </div>
</div>

<form action="{{ route('performance.reports.update', $report['id'] ?? 1) }}" method="POST" id="editReportForm">
    @csrf
    @method('PUT')
    
    <!-- Report Details -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Report Details</h3>
            <span class="report-status {{ $report['status'] ?? 'pending' }}">
                @if(($report['status'] ?? '') == 'completed')
                    <i class="fas fa-check-circle"></i> Completed
                @elseif(($report['status'] ?? '') == 'pending')
                    <i class="fas fa-clock"></i> Pending
                @else
                    <i class="fas fa-hourglass-half"></i> {{ ucfirst($report['status'] ?? 'Draft') }}
                @endif
            </span>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Report Title</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $report['title'] ?? '') }}" required>
                    @error('title')
                        <small class="form-error">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $report['due_date'] ?? '') }}" required>
                    @error('due_date')
                        <small class="form-error">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    
    <!-- Performance Evaluation Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Performance Evaluation</h3>
            <small class="card-subtitle">Complete your performance assessment</small>
        </div>
        <div class="card-body">
            <!-- Overall Performance Section -->
            <div class="evaluation-section">
                <h4 class="section-title">
                    <i class="fas fa-star"></i>
                    Overall Performance
                </h4>
                
                <div class="form-group">
                    <label class="form-label">How would you rate your overall performance this period?</label>
                    <div class="rating-input">
                        <div class="star-rating" data-rating="4">
                            <i class="fas fa-star" data-value="1"></i>
                            <i class="fas fa-star" data-value="2"></i>
                            <i class="fas fa-star" data-value="3"></i>
                            <i class="fas fa-star" data-value="4"></i>
                            <i class="fas fa-star" data-value="5"></i>
                        </div>
                        <input type="hidden" name="overall_rating" value="4">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Please describe your key achievements this period:</label>
                    <textarea name="key_achievements" class="form-control" rows="4" placeholder="Describe your major accomplishments and successes...">{{ old('key_achievements', 'Successfully completed the Q3 sales targets, exceeding goals by 15%. Led the customer onboarding project that improved client satisfaction scores.') }}</textarea>
                </div>
            </div>
            
            <!-- Goals and Objectives Section -->
            <div class="evaluation-section">
                <h4 class="section-title">
                    <i class="fas fa-bullseye"></i>
                    Goals and Objectives
                </h4>
                
                <div class="form-group">
                    <label class="form-label">Did you meet your quarterly goals?</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="goals_met" value="yes" checked>
                            <span class="radio-label">Yes</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="goals_met" value="partially">
                            <span class="radio-label">Partially</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="goals_met" value="no">
                            <span class="radio-label">No</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">What challenges did you face and how did you overcome them?</label>
                    <textarea name="challenges_overcome" class="form-control" rows="4" placeholder="Describe any obstacles and your solutions...">{{ old('challenges_overcome', 'The main challenge was adapting to the new CRM system mid-quarter. I overcame this by dedicating extra time to training and collaborating closely with the IT team.') }}</textarea>
                </div>
            </div>
            
            <!-- Skills Assessment Section -->
            <div class="evaluation-section">
                <h4 class="section-title">
                    <i class="fas fa-chart-line"></i>
                    Skills Assessment
                </h4>
                
                @php
                    $skills = [
                        ['name' => 'Quality of Work', 'value' => 9],
                        ['name' => 'Communication', 'value' => 8],
                        ['name' => 'Teamwork', 'value' => 9],
                        ['name' => 'Initiative', 'value' => 8],
                        ['name' => 'Problem Solving', 'value' => 8],
                    ];
                @endphp
                
                <div class="skills-grid">
                    @foreach($skills as $skill)
                    <div class="skill-item">
                        <label class="skill-label">{{ $skill['name'] }}</label>
                        <div class="skill-rating">
                            <input type="range" name="skill_{{ strtolower(str_replace(' ', '_', $skill['name'])) }}" min="1" max="10" value="{{ $skill['value'] }}" class="skill-slider">
                            <div class="skill-value">{{ $skill['value'] }}/10</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Development Section -->
            <div class="evaluation-section">
                <h4 class="section-title">
                    <i class="fas fa-graduation-cap"></i>
                    Development and Growth
                </h4>
                
                <div class="form-group">
                    <label class="form-label">What areas would you like to develop further?</label>
                    <textarea name="development_areas" class="form-control" rows="3" placeholder="Areas for improvement and growth...">{{ old('development_areas', 'I would like to improve my public speaking skills and learn more about data analytics to better support strategic decision-making.') }}</textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">What support do you need from management?</label>
                    <textarea name="support_needed" class="form-control" rows="3" placeholder="Support and resources you need...">{{ old('support_needed', 'I would appreciate opportunities to attend workshops on data analytics and presentation skills. Access to leadership training programs would be valuable.') }}</textarea>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="form-actions">
        <button type="button" class="btn btn-outline" onclick="window.history.back()">Cancel</button>
        <button type="button" class="btn btn-secondary" onclick="saveDraft()">
            <i class="fas fa-save"></i>
            Save as Draft
        </button>
        <button type="submit" class="btn btn-success" id="submitReportBtn">
            <i class="fas fa-check"></i>
            Submit for Review
        </button>
    </div>
</form>
@endsection

@push('styles')
<style>
.report-status {
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
    font-weight: 600;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.report-status.completed {
    background-color: var(--success-color);
    color: var(--white);
}

.report-status.pending {
    background-color: var(--warning-color);
    color: var(--white);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-lg);
}

.form-error {
    color: var(--danger-color);
    font-size: 0.75rem;
    margin-top: 0.25rem;
    display: block;
}

.evaluation-section {
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);
    margin-bottom: var(--spacing-xl);
    overflow: hidden;
}

.section-title {
    background: var(--bg-secondary);
    padding: var(--spacing-lg);
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    border-bottom: 1px solid var(--border-color);
}

.evaluation-section .card-body,
.evaluation-section > div:last-child {
    padding: var(--spacing-lg);
}

.rating-input {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
}

.star-rating {
    display: flex;
    gap: 0.25rem;
    cursor: pointer;
}

.star-rating i {
    font-size: 1.5rem;
    color: var(--text-muted);
    transition: color 0.2s ease;
}

.star-rating i.active,
.star-rating i:hover,
.star-rating:hover i:hover ~ i {
    color: var(--warning-color);
}

.radio-group {
    display: flex;
    gap: var(--spacing-lg);
    flex-wrap: wrap;
}

.radio-option {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    cursor: pointer;
}

.radio-option input[type="radio"] {
    margin: 0;
}

.radio-label {
    font-weight: 500;
    color: var(--text-primary);
}

.skills-grid {
    display: grid;
    gap: var(--spacing-lg);
}

.skill-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--spacing-lg);
    padding: var(--spacing-md);
    background: var(--bg-secondary);
    border-radius: var(--border-radius);
}

.skill-label {
    font-weight: 600;
    color: var(--text-primary);
    min-width: 150px;
}

.skill-rating {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    flex: 1;
}

.skill-slider {
    flex: 1;
    height: 6px;
    background: var(--border-color);
    border-radius: 3px;
    outline: none;
    appearance: none;
}

.skill-slider::-webkit-slider-thumb {
    appearance: none;
    width: 20px;
    height: 20px;
    background: var(--primary-color);
    border-radius: 50%;
    cursor: pointer;
}

.skill-slider::-moz-range-thumb {
    width: 20px;
    height: 20px;
    background: var(--primary-color);
    border-radius: 50%;
    cursor: pointer;
    border: none;
}

.skill-value {
    font-weight: 700;
    color: var(--primary-color);
    min-width: 40px;
    text-align: center;
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
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .radio-group {
        flex-direction: column;
        gap: var(--spacing-sm);
    }
    
    .skill-item {
        flex-direction: column;
        align-items: stretch;
        gap: var(--spacing-sm);
    }
    
    .skill-rating {
        justify-content: space-between;
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
    // Star rating functionality
    const starRatings = document.querySelectorAll('.star-rating');
    
    starRatings.forEach(rating => {
        const stars = rating.querySelectorAll('i');
        const hiddenInput = rating.parentNode.querySelector('input[type="hidden"]');
        let currentRating = parseInt(rating.dataset.rating) || 0;
        
        // Set initial state
        updateStars(stars, currentRating);
        
        stars.forEach((star, index) => {
            star.addEventListener('click', () => {
                currentRating = index + 1;
                hiddenInput.value = currentRating;
                updateStars(stars, currentRating);
            });
            
            star.addEventListener('mouseenter', () => {
                updateStars(stars, index + 1);
            });
        });
        
        rating.addEventListener('mouseleave', () => {
            updateStars(stars, currentRating);
        });
    });
    
    function updateStars(stars, rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }
    
    // Skill sliders
    const skillSliders = document.querySelectorAll('.skill-slider');
    skillSliders.forEach(slider => {
        const valueDisplay = slider.parentNode.querySelector('.skill-value');
        
        slider.addEventListener('input', function() {
            valueDisplay.textContent = this.value + '/10';
        });
    });
    
    // Form submission
    const form = document.getElementById('editReportForm');
    const submitBtn = document.getElementById('submitReportBtn');
    
    form.addEventListener('submit', function(e) {
        if (submitBtn.textContent.includes('Submit for Review')) {
            const confirmed = confirm('Are you sure you want to submit this report for review? You will not be able to edit it after submission.');
            if (!confirmed) {
                e.preventDefault();
                return;
            }
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    });
});

function saveDraft() {
    const form = document.getElementById('editReportForm');
    const formData = new FormData(form);
    formData.append('save_as_draft', '1');
    
    if (window.PMS && window.PMS.notifications) {
        window.PMS.notifications.info('Saving draft...');
        
        // In a real implementation, make AJAX call to save draft
        setTimeout(() => {
            window.PMS.notifications.success('Draft saved successfully!');
        }, 1000);
    } else {
        alert('Draft saved successfully!');
    }
}
</script>
@endpush
