@extends('layouts.pms-app')

@section('title', 'Create New Report')

@section('page-title', 'Create New Report')
@section('page-subtitle', 'Start a new performance report')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Create New Performance Report</h1>
        <p class="page-subtitle">Select a template and fill in the details to create your report</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('performance.reports.index') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i>
            Back to Reports
        </a>
    </div>
</div>

<form action="{{ route('performance.reports.store') }}" method="POST" id="createReportForm">
    @csrf
    
    <div class="dashboard-grid layout">
        <!-- Report Details -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Report Details</h3>
                <small class="card-subtitle">Basic information about your performance report</small>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Report Title <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="e.g., Q4 2024 Performance Review" value="{{ old('title') }}" required>
                    @error('title')
                        <small class="form-error">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Brief description of this report">{{ old('description') }}</textarea>
                    @error('description')
                        <small class="form-error">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Due Date <span class="required">*</span></label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}" required>
                    @error('due_date')
                        <small class="form-error">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Review Period</label>
                    <div class="date-range">
                        <input type="date" name="period_start" class="form-control" placeholder="Start Date" value="{{ old('period_start') }}">
                        <span class="date-separator">to</span>
                        <input type="date" name="period_end" class="form-control" placeholder="End Date" value="{{ old('period_end') }}">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Template Selection -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Select Template</h3>
                <small class="card-subtitle">Choose a template for your performance report</small>
            </div>
            <div class="card-body">
                <div class="template-selection">
                    @foreach($templates ?? [] as $template)
                    <div class="template-option">
                        <label class="template-card">
                            <input type="radio" name="template_id" value="{{ $template['id'] }}" {{ old('template_id') == $template['id'] ? 'checked' : '' }} required>
                            <div class="template-card-content">
                                <div class="template-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="template-info">
                                    <h4 class="template-name">{{ $template['name'] }}</h4>
                                    <p class="template-description">{{ $template['description'] ?? 'Standard performance evaluation template' }}</p>
                                    <div class="template-meta">
                                        <span class="template-usage">Used {{ $template['usage_count'] ?? 0 }} times</span>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                    @endforeach
                    
                    @if(empty($templates))
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="empty-state-title">No Templates Available</div>
                        <div class="empty-state-description">
                            Please create a template first before creating reports.
                        </div>
                        <div class="empty-state-actions">
                            <a href="{{ route('performance.templates.builder') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Create Template
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
                
                @error('template_id')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="form-actions">
        <button type="button" class="btn btn-outline" onclick="window.history.back()">Cancel</button>
        <button type="submit" class="btn btn-success" id="createReportBtn">
            <i class="fas fa-plus"></i>
            Create Report
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

.date-range {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
}

.date-separator {
    color: var(--text-muted);
    font-size: 0.875rem;
}

.template-selection {
    display: grid;
    gap: var(--spacing-md);
}

.template-option {
    width: 100%;
}

.template-card {
    display: block;
    cursor: pointer;
    border: 2px solid var(--border-color);
    border-radius: var(--border-radius-lg);
    transition: var(--transition);
    overflow: hidden;
}

.template-card:hover {
    border-color: var(--primary-color);
    box-shadow: var(--shadow);
}

.template-card input[type="radio"] {
    display: none;
}

.template-card input[type="radio"]:checked + .template-card-content {
    border-color: var(--primary-color);
    background-color: rgba(79, 70, 229, 0.05);
}

.template-card-content {
    display: flex;
    align-items: center;
    gap: var(--spacing-lg);
    padding: var(--spacing-lg);
    border: 2px solid transparent;
    border-radius: var(--border-radius-lg);
    transition: var(--transition);
}

.template-icon {
    width: 3rem;
    height: 3rem;
    background: var(--primary-color);
    color: var(--white);
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.template-info {
    flex: 1;
}

.template-name {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 0.25rem 0;
}

.template-description {
    color: var(--text-secondary);
    margin: 0 0 0.5rem 0;
    font-size: 0.875rem;
    line-height: 1.4;
}

.template-meta {
    display: flex;
    gap: var(--spacing-md);
}

.template-usage {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: var(--spacing-md);
    margin-top: var(--spacing-xl);
    padding-top: var(--spacing-lg);
    border-top: 1px solid var(--border-color);
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

@media (max-width: 768px) {
    .dashboard-grid.layout {
        grid-template-columns: 1fr;
    }
    
    .date-range {
        flex-direction: column;
        align-items: stretch;
    }
    
    .date-separator {
        text-align: center;
    }
    
    .template-card-content {
        flex-direction: column;
        text-align: center;
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
    const form = document.getElementById('createReportForm');
    const submitBtn = document.getElementById('createReportBtn');
    
    form.addEventListener('submit', function(e) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Report...';
    });
    
    // Template selection feedback
    const templateRadios = document.querySelectorAll('input[name="template_id"]');
    templateRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Remove selected state from all cards
            document.querySelectorAll('.template-card-content').forEach(card => {
                card.style.borderColor = 'transparent';
                card.style.backgroundColor = 'transparent';
            });
            
            // Add selected state to chosen card
            if (this.checked) {
                const cardContent = this.nextElementSibling;
                cardContent.style.borderColor = 'var(--primary-color)';
                cardContent.style.backgroundColor = 'rgba(79, 70, 229, 0.05)';
            }
        });
    });
});
</script>
@endpush
