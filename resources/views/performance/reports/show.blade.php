@extends('layouts.pms-app')

@section('title', 'View Report')

@section('page-title', 'View Report')
@section('page-subtitle', 'Performance report details and review')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $report['title'] ?? 'Performance Report' }}</h1>
        <p class="page-subtitle">Review the details and results of this performance report</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('performance.reports.index') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i>
            Back to Reports
        </a>
        @if(($report['status'] ?? '') == 'pending')
        <a href="{{ route('performance.reports.edit', $report['id'] ?? 1) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i>
            Edit Report
        </a>
        @endif
        <button class="btn btn-outline" onclick="printReport()">
            <i class="fas fa-print"></i>
            Print
        </button>
    </div>
</div>

<!-- Report Overview -->
<div class="dashboard-grid layout">
    <!-- Report Information -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Report Information</h3>
            <span class="report-status {{ $report['status'] ?? 'pending' }}">
                @if(($report['status'] ?? '') == 'completed')
                    <i class="fas fa-check-circle"></i> Completed
                @elseif(($report['status'] ?? '') == 'pending')
                    <i class="fas fa-clock"></i> Pending
                @else
                    <i class="fas fa-hourglass-half"></i> {{ ucfirst($report['status'] ?? 'Unknown') }}
                @endif
            </span>
        </div>
        <div class="card-body">
            <div class="report-details">
                <div class="detail-item">
                    <label class="detail-label">Template Used:</label>
                    <span class="detail-value">{{ $report['template'] ?? 'Standard Review' }}</span>
                </div>
                
                <div class="detail-item">
                    <label class="detail-label">Due Date:</label>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($report['due_date'] ?? now())->format('F d, Y') }}</span>
                </div>
                
                @if($report['submitted_at'] ?? false)
                <div class="detail-item">
                    <label class="detail-label">Submitted:</label>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($report['submitted_at'])->format('F d, Y \a\t g:i A') }}</span>
                </div>
                @endif
                
                @if($report['score'] ?? false)
                <div class="detail-item">
                    <label class="detail-label">Overall Score:</label>
                    <span class="detail-value score-highlight">{{ number_format($report['score'], 1) }}%</span>
                </div>
                @endif
                
                <div class="detail-item">
                    <label class="detail-label">Reviewer:</label>
                    <span class="detail-value">{{ $report['reviewer'] ?? 'Manager' }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Score Breakdown -->
    @if($report['score'] ?? false)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Score Breakdown</h3>
        </div>
        <div class="card-body">
            <div class="score-breakdown">
                @php
                    $categories = [
                        ['name' => 'Quality of Work', 'score' => 92],
                        ['name' => 'Communication', 'score' => 88],
                        ['name' => 'Teamwork', 'score' => 85],
                        ['name' => 'Initiative', 'score' => 90],
                        ['name' => 'Problem Solving', 'score' => 87],
                    ];
                @endphp
                
                @foreach($categories as $category)
                <div class="score-item">
                    <div class="score-category">
                        <span class="category-name">{{ $category['name'] }}</span>
                        <span class="category-score">{{ $category['score'] }}%</span>
                    </div>
                    <div class="score-bar">
                        <div class="score-fill" style="width: {{ $category['score'] }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Report Content -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Performance Review Details</h3>
    </div>
    <div class="card-body">
        <div class="review-sections">
            <!-- Sample Review Section -->
            <div class="review-section">
                <h4 class="section-title">
                    <i class="fas fa-star"></i>
                    Overall Performance
                </h4>
                <div class="section-content">
                    <div class="question-answer">
                        <p class="question">How would you rate your overall performance this quarter?</p>
                        <div class="answer">
                            <div class="rating-display">
                                <div class="stars">
                                    <i class="fas fa-star active"></i>
                                    <i class="fas fa-star active"></i>
                                    <i class="fas fa-star active"></i>
                                    <i class="fas fa-star active"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <span class="rating-text">4 out of 5</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="question-answer">
                        <p class="question">Please describe your key achievements this period:</p>
                        <div class="answer text-answer">
                            Successfully completed the Q3 sales targets, exceeding goals by 15%. Led the customer onboarding project that improved client satisfaction scores. Mentored two junior team members and helped them achieve their quarterly objectives.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Goals and Objectives -->
            <div class="review-section">
                <h4 class="section-title">
                    <i class="fas fa-bullseye"></i>
                    Goals and Objectives
                </h4>
                <div class="section-content">
                    <div class="question-answer">
                        <p class="question">Did you meet your quarterly goals?</p>
                        <div class="answer">
                            <span class="yes-no-answer yes">Yes</span>
                        </div>
                    </div>
                    
                    <div class="question-answer">
                        <p class="question">What challenges did you face and how did you overcome them?</p>
                        <div class="answer text-answer">
                            The main challenge was adapting to the new CRM system mid-quarter. I overcame this by dedicating extra time to training and collaborating closely with the IT team to understand best practices. This investment paid off as I became more efficient by the end of the period.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Development Areas -->
            <div class="review-section">
                <h4 class="section-title">
                    <i class="fas fa-chart-line"></i>
                    Development and Growth
                </h4>
                <div class="section-content">
                    <div class="question-answer">
                        <p class="question">What areas would you like to develop further?</p>
                        <div class="answer text-answer">
                            I would like to improve my public speaking skills and learn more about data analytics to better support strategic decision-making. Additionally, I'm interested in developing leadership skills through mentoring opportunities.
                        </div>
                    </div>
                    
                    <div class="question-answer">
                        <p class="question">What support do you need from management?</p>
                        <div class="answer text-answer">
                            I would appreciate opportunities to attend workshops on data analytics and presentation skills. Also, access to leadership training programs would be valuable for my career development.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Manager Comments (if applicable) -->
@if(($report['status'] ?? '') == 'completed')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Manager Comments</h3>
    </div>
    <div class="card-body">
        <div class="manager-comments">
            <div class="comment-section">
                <h5 class="comment-title">Strengths</h5>
                <p class="comment-text">
                    Excellent performance this quarter. Consistently delivers high-quality work and shows great initiative. Leadership skills are evident in how they mentor junior team members. Communication with clients has been outstanding.
                </p>
            </div>
            
            <div class="comment-section">
                <h5 class="comment-title">Areas for Improvement</h5>
                <p class="comment-text">
                    Continue developing analytical skills and consider taking on more strategic projects. Time management during peak periods could be improved with better prioritization techniques.
                </p>
            </div>
            
            <div class="comment-section">
                <h5 class="comment-title">Development Plan</h5>
                <p class="comment-text">
                    Enroll in the advanced analytics course next quarter. Schedule monthly one-on-ones to discuss leadership development opportunities. Consider for promotion to senior role in next review cycle.
                </p>
            </div>
        </div>
    </div>
</div>
@endif
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

.report-details {
    display: grid;
    gap: var(--spacing-md);
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-sm) 0;
    border-bottom: 1px solid var(--border-color);
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.detail-value {
    color: var(--text-primary);
    font-weight: 500;
}

.score-highlight {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--success-color);
}

.score-breakdown {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
}

.score-item {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.score-category {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.category-name {
    font-weight: 600;
    color: var(--text-primary);
}

.category-score {
    font-weight: 700;
    color: var(--primary-color);
}

.score-bar {
    height: 8px;
    background-color: var(--bg-secondary);
    border-radius: 4px;
    overflow: hidden;
}

.score-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
    border-radius: 4px;
    transition: width 0.3s ease;
}

.review-sections {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xl);
}

.review-section {
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);
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

.section-content {
    padding: var(--spacing-lg);
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
}

.question-answer {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.question {
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.answer {
    margin-left: var(--spacing-md);
}

.text-answer {
    background: var(--bg-secondary);
    padding: var(--spacing-md);
    border-radius: var(--border-radius);
    color: var(--text-primary);
    line-height: 1.6;
}

.rating-display {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
}

.stars {
    display: flex;
    gap: 0.125rem;
}

.stars i {
    color: var(--text-muted);
    font-size: 1.125rem;
}

.stars i.active {
    color: var(--warning-color);
}

.rating-text {
    font-weight: 600;
    color: var(--text-primary);
}

.yes-no-answer {
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.yes-no-answer.yes {
    background-color: var(--success-color);
    color: var(--white);
}

.yes-no-answer.yes::before {
    content: "✓";
}

.yes-no-answer.no {
    background-color: var(--danger-color);
    color: var(--white);
}

.yes-no-answer.no::before {
    content: "✗";
}

.manager-comments {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xl);
}

.comment-section {
    border-left: 4px solid var(--primary-color);
    padding-left: var(--spacing-lg);
}

.comment-title {
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 var(--spacing-sm) 0;
    font-size: 1rem;
}

.comment-text {
    color: var(--text-primary);
    line-height: 1.6;
    margin: 0;
}

@media (max-width: 768px) {
    .dashboard-grid.layout {
        grid-template-columns: 1fr;
    }
    
    .detail-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    
    .page-actions {
        flex-direction: column;
        gap: var(--spacing-sm);
    }
    
    .rating-display {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-sm);
    }
}
</style>
@endpush

@push('scripts')
<script>
function printReport() {
    window.print();
}
</script>
@endpush
