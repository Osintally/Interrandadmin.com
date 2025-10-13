@extends('layouts.pms-app')

@section('title', 'Create Template')

@section('page-title', 'Create New Template')
@section('page-subtitle', 'Set up a new performance evaluation template')

@section('content')
<div class="page-actions">
    <a href="{{ route('performance.templates.index') }}" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i>
        Back to Templates
    </a>
</div>

<!-- Template Creation Form -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Template Information</h3>
        <p class="card-subtitle">Configure basic template information and settings</p>
    </div>
    
    <div class="card-body">
        <form action="{{ route('performance.templates.store') }}" method="POST" id="templateCreateForm">
            @csrf
            
            <div class="form-grid">
                <!-- Template Name -->
                <div class="form-group">
                    <label for="name" class="form-label required">Template Name</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name') }}" 
                           placeholder="Enter template name"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Template Description -->
                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" 
                              name="description" 
                              class="form-control @error('description') is-invalid @enderror" 
                              rows="3" 
                              placeholder="Describe the purpose and scope of this template">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Template Category -->
                <div class="form-group">
                    <label for="category" class="form-label">Category</label>
                    <select id="category" 
                            name="category" 
                            class="form-control @error('category') is-invalid @enderror">
                        <option value="performance" {{ old('category', 'performance') == 'performance' ? 'selected' : '' }}>
                            Performance Review
                        </option>
                        <option value="quarterly" {{ old('category') == 'quarterly' ? 'selected' : '' }}>
                            Quarterly Review
                        </option>
                        <option value="annual" {{ old('category') == 'annual' ? 'selected' : '' }}>
                            Annual Review
                        </option>
                        <option value="probation" {{ old('category') == 'probation' ? 'selected' : '' }}>
                            Probation Review
                        </option>
                        <option value="custom" {{ old('category') == 'custom' ? 'selected' : '' }}>
                            Custom Review
                        </option>
                    </select>
                    @error('category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Department -->
                <div class="form-group">
                    <label for="department_id" class="form-label">Department</label>
                    <select id="department_id" 
                            name="department_id" 
                            class="form-control @error('department_id') is-invalid @enderror">
                        <option value="">All Departments</option>
                        @foreach($departments ?? [] as $department)
                            <option value="{{ $department['id'] }}" 
                                    {{ old('department_id') == $department['id'] ? 'selected' : '' }}>
                                {{ $department['name'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Submission Deadline -->
                <div class="form-group">
                    <label for="submission_deadline" class="form-label">Submission Deadline</label>
                    <input type="date" 
                           id="submission_deadline" 
                           name="submission_deadline" 
                           class="form-control @error('submission_deadline') is-invalid @enderror" 
                           value="{{ old('submission_deadline') }}">
                    @error('submission_deadline')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Max Submissions -->
                <div class="form-group">
                    <label for="max_submissions" class="form-label">Max Submissions per Employee</label>
                    <input type="number" 
                           id="max_submissions" 
                           name="max_submissions" 
                           class="form-control @error('max_submissions') is-invalid @enderror" 
                           value="{{ old('max_submissions') }}" 
                           min="1" 
                           max="10"
                           placeholder="Leave empty for unlimited">
                    @error('max_submissions')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Template Options -->
            <div class="form-section">
                <h4 class="form-section-title">Template Options</h4>
                
                <div class="form-options">
                    <div class="form-option">
                        <div class="form-check">
                            <input type="checkbox" 
                                   id="is_public" 
                                   name="is_public" 
                                   class="form-check-input" 
                                   value="1" 
                                   {{ old('is_public') ? 'checked' : '' }}>
                            <label for="is_public" class="form-check-label">
                                <strong>Public Template</strong>
                                <span class="form-check-description">Make this template available to all departments</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-option">
                        <div class="form-check">
                            <input type="checkbox" 
                                   id="allow_anonymous" 
                                   name="allow_anonymous" 
                                   class="form-check-input" 
                                   value="1" 
                                   {{ old('allow_anonymous') ? 'checked' : '' }}>
                            <label for="allow_anonymous" class="form-check-label">
                                <strong>Allow Anonymous Submissions</strong>
                                <span class="form-check-description">Allow employees to submit reviews anonymously</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-option">
                        <div class="form-check">
                            <input type="checkbox" 
                                   id="collect_email" 
                                   name="collect_email" 
                                   class="form-check-input" 
                                   value="1" 
                                   {{ old('collect_email', true) ? 'checked' : '' }}>
                            <label for="collect_email" class="form-check-label">
                                <strong>Collect Email Addresses</strong>
                                <span class="form-check-description">Require email addresses for submissions</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" class="btn btn-outline" onclick="history.back()">
                    <i class="fas fa-times"></i>
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Create Template
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Start Guide -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quick Start Guide</h3>
    </div>
    <div class="card-body">
        <div class="guide-steps">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h4>Create Template</h4>
                    <p>Fill in the basic information above and click "Create Template"</p>
                </div>
            </div>
            
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h4>Design Structure</h4>
                    <p>Use the Template Builder to add sections, fields, and configure the evaluation structure</p>
                </div>
            </div>
            
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h4>Test & Publish</h4>
                    <p>Preview your template, make adjustments, and publish it for use</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.875rem;
}

.form-label.required::after {
    content: ' *';
    color: #ef4444;
}

.form-control {
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    font-size: 0.875rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-control.is-invalid {
    border-color: #ef4444;
}

.invalid-feedback {
    color: #ef4444;
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.form-section {
    margin: 2rem 0;
    padding: 1.5rem;
    background: var(--bg-secondary);
    border-radius: var(--border-radius);
}

.form-section-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 1rem 0;
}

.form-options {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-option {
    padding: 1rem;
    background: var(--white);
    border-radius: var(--border-radius);
    border: 1px solid var(--border-color);
}

.form-check {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.form-check-input {
    margin: 0;
    width: 1.25rem;
    height: 1.25rem;
    cursor: pointer;
}

.form-check-label {
    flex: 1;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.form-check-description {
    font-size: 0.875rem;
    color: var(--text-muted);
    font-weight: normal;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    padding-top: 2rem;
    border-top: 1px solid var(--border-color);
    margin-top: 2rem;
}

.guide-steps {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.step {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.step-number {
    width: 2.5rem;
    height: 2.5rem;
    background: var(--primary-color);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.125rem;
    flex-shrink: 0;
}

.step-content h4 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 0.5rem 0;
}

.step-content p {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .guide-steps {
        gap: 1rem;
    }
    
    .step {
        flex-direction: column;
        text-align: center;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('templateCreateForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
        submitBtn.disabled = true;
        
        // Submit form
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            }
            throw new Error('Creation failed');
        })
        .then(data => {
            // Show success message
            showToast('success', 'Template created successfully');
            
            // Redirect to template builder
            setTimeout(() => {
                window.location.href = data.redirect_url || '{{ route("performance.templates.builder") }}';
            }, 1500);
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Failed to create template');
            
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
});

function showToast(type, message) {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Remove toast
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => document.body.removeChild(toast), 300);
    }, 3000);
}
</script>
@endpush
