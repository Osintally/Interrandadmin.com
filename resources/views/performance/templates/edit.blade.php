@extends('layouts.pms-app')

@section('title', 'Edit Template')

@section('page-title', 'Edit Template')
@section('page-subtitle', 'Modify template settings and configuration')

@section('content')
<div class="page-actions">
    <a href="{{ route('performance.templates.show', $template['id']) }}" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i>
        Back to Template
    </a>
    <a href="{{ route('performance.templates.builder', $template['id']) }}" class="btn btn-primary">
        <i class="fas fa-tools"></i>
        Open Builder
    </a>
</div>

<!-- Template Edit Form -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Template Settings</h3>
        <p class="card-subtitle">Configure basic template information and settings</p>
    </div>
    
    <div class="card-body">
        <form action="{{ route('performance.templates.update', $template['id']) }}" method="POST" id="templateEditForm">
            @csrf
            @method('PUT')
            
            <div class="form-grid">
                <!-- Template Name -->
                <div class="form-group">
                    <label for="name" class="form-label required">Template Name</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $template['name']) }}" 
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
                              placeholder="Describe the purpose and scope of this template">{{ old('description', $template['description']) }}</textarea>
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
                        <option value="performance" {{ old('category', $template['category'] ?? 'performance') == 'performance' ? 'selected' : '' }}>
                            Performance Review
                        </option>
                        <option value="quarterly" {{ old('category', $template['category'] ?? '') == 'quarterly' ? 'selected' : '' }}>
                            Quarterly Review
                        </option>
                        <option value="annual" {{ old('category', $template['category'] ?? '') == 'annual' ? 'selected' : '' }}>
                            Annual Review
                        </option>
                        <option value="probation" {{ old('category', $template['category'] ?? '') == 'probation' ? 'selected' : '' }}>
                            Probation Review
                        </option>
                        <option value="custom" {{ old('category', $template['category'] ?? '') == 'custom' ? 'selected' : '' }}>
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
                                    {{ old('department_id', $template['department_id'] ?? '') == $department['id'] ? 'selected' : '' }}>
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
                           value="{{ old('submission_deadline', $template['submission_deadline'] ?? '') }}">
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
                           value="{{ old('max_submissions', $template['max_submissions'] ?? '') }}" 
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
                                   {{ old('is_public', $template['is_public'] ?? false) ? 'checked' : '' }}>
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
                                   {{ old('allow_anonymous', $template['allow_anonymous'] ?? false) ? 'checked' : '' }}>
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
                                   {{ old('collect_email', $template['collect_email'] ?? true) ? 'checked' : '' }}>
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
                    Update Template
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Template Structure Editor -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Template Structure</h3>
        <p class="card-subtitle">Edit the fields and sections of your template</p>
        <div class="card-actions">
            <button type="button" class="btn btn-outline btn-sm" onclick="addSection()">
                <i class="fas fa-plus"></i>
                Add Section
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="addField()">
                <i class="fas fa-plus"></i>
                Add Field
            </button>
        </div>
    </div>
    <div class="card-body">
        <div id="templateStructure">
            <!-- Sections will be rendered here -->
            @if(!empty($template['sections']) && count($template['sections']) > 0)
                @foreach($template['sections'] as $sectionIndex => $section)
                    <div class="section-editor" data-section-index="{{ $sectionIndex }}">
                        <div class="section-header">
                            <div class="section-title-input">
                                <input type="text" 
                                       name="sections[{{ $sectionIndex }}][title]" 
                                       value="{{ $section['title'] ?? '' }}" 
                                       placeholder="Section Title"
                                       class="form-control">
                            </div>
                            <div class="section-actions">
                                <button type="button" class="btn btn-outline btn-sm" onclick="addFieldToSection({{ $sectionIndex }})">
                                    <i class="fas fa-plus"></i>
                                    Add Field
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeSection({{ $sectionIndex }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="section-description-input">
                            <textarea name="sections[{{ $sectionIndex }}][description]" 
                                      placeholder="Section Description (optional)"
                                      class="form-control"
                                      rows="2">{{ $section['description'] ?? '' }}</textarea>
                        </div>
                        
                        <div class="section-fields">
                            @if(!empty($section['fields']) && count($section['fields']) > 0)
                                @foreach($section['fields'] as $fieldIndex => $field)
                                    <div class="field-editor" data-field-index="{{ $fieldIndex }}">
                                        <div class="field-row">
                                            <div class="field-inputs">
                                                <input type="text" 
                                                       name="sections[{{ $sectionIndex }}][fields][{{ $fieldIndex }}][label]" 
                                                       value="{{ $field['label'] ?? '' }}" 
                                                       placeholder="Field Label"
                                                       class="form-control">
                                                <select name="sections[{{ $sectionIndex }}][fields][{{ $fieldIndex }}][type]" 
                                                        class="form-control">
                                                    <option value="text" {{ ($field['type'] ?? '') == 'text' ? 'selected' : '' }}>Text Input</option>
                                                    <option value="textarea" {{ ($field['type'] ?? '') == 'textarea' ? 'selected' : '' }}>Text Area</option>
                                                    <option value="select" {{ ($field['type'] ?? '') == 'select' ? 'selected' : '' }}>Dropdown</option>
                                                    <option value="radio" {{ ($field['type'] ?? '') == 'radio' ? 'selected' : '' }}>Radio Buttons</option>
                                                    <option value="checkbox" {{ ($field['type'] ?? '') == 'checkbox' ? 'selected' : '' }}>Checkboxes</option>
                                                    <option value="rating" {{ ($field['type'] ?? '') == 'rating' ? 'selected' : '' }}>Rating Scale</option>
                                                    <option value="date" {{ ($field['type'] ?? '') == 'date' ? 'selected' : '' }}>Date Picker</option>
                                                    <option value="number" {{ ($field['type'] ?? '') == 'number' ? 'selected' : '' }}>Number Input</option>
                                                </select>
                                                <input type="text" 
                                                       name="sections[{{ $sectionIndex }}][fields][{{ $fieldIndex }}][placeholder]" 
                                                       value="{{ $field['placeholder'] ?? '' }}" 
                                                       placeholder="Placeholder text"
                                                       class="form-control">
                                            </div>
                                            <div class="field-actions">
                                                <label class="field-required">
                                                    <input type="checkbox" 
                                                           name="sections[{{ $sectionIndex }}][fields][{{ $fieldIndex }}][required]" 
                                                           value="1" 
                                                           {{ ($field['required'] ?? false) ? 'checked' : '' }}>
                                                    Required
                                                </label>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="removeField({{ $sectionIndex }}, {{ $fieldIndex }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        @if(($field['type'] ?? '') == 'select' || ($field['type'] ?? '') == 'radio' || ($field['type'] ?? '') == 'checkbox')
                                            <div class="field-options">
                                                <label>Options (one per line):</label>
                                                <textarea name="sections[{{ $sectionIndex }}][fields][{{ $fieldIndex }}][options]" 
                                                          class="form-control"
                                                          rows="3"
                                                          placeholder="Option 1&#10;Option 2&#10;Option 3">{{ is_array($field['options'] ?? []) ? implode("\n", $field['options']) : ($field['options'] ?? '') }}</textarea>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="empty-fields">
                                    <p>No fields in this section. Click "Add Field" to get started.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-sections">
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <h4>No sections defined</h4>
                        <p>Create sections to organize your template fields</p>
                        <button type="button" class="btn btn-primary" onclick="addSection()">
                            <i class="fas fa-plus"></i>
                            Add First Section
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Template Statistics -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Template Statistics</h3>
    </div>
    <div class="card-body">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-icon bg-primary">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $template['usage_count'] ?? 0 }}</div>
                    <div class="stat-label">Times Used</div>
                </div>
            </div>
            
            <div class="stat-item">
                <div class="stat-icon bg-success">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ \Carbon\Carbon::parse($template['created_at'])->format('M d') }}</div>
                    <div class="stat-label">Created</div>
                </div>
            </div>
            
            <div class="stat-item">
                <div class="stat-icon bg-info">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $template['department_count'] ?? 0 }}</div>
                    <div class="stat-label">Departments</div>
                </div>
            </div>
            
            <div class="stat-item">
                <div class="stat-icon bg-warning">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $template['field_count'] ?? 0 }}</div>
                    <div class="stat-label">Fields</div>
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

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--bg-secondary);
    border-radius: var(--border-radius);
}

.stat-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-muted);
}

/* Template Structure Editor Styles */
.card-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.section-editor {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    background: #f9fafb;
    overflow: hidden;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
}

.section-title-input {
    flex: 1;
    margin-right: 1rem;
}

.section-title-input input {
    font-weight: 600;
    font-size: 1.125rem;
}

.section-actions {
    display: flex;
    gap: 0.5rem;
}

.section-description-input {
    padding: 1rem;
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
}

.section-fields {
    padding: 1rem;
}

.field-editor {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    margin-bottom: 1rem;
    padding: 1rem;
}

.field-row {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.field-inputs {
    flex: 1;
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 0.75rem;
}

.field-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-end;
}

.field-required {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
    cursor: pointer;
}

.field-required input[type="checkbox"] {
    margin: 0;
}

.field-options {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.field-options label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
    display: block;
}

.empty-fields {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
    background: #f9fafb;
    border-radius: 6px;
    border: 2px dashed #d1d5db;
}

.empty-sections {
    text-align: center;
    padding: 3rem 1rem;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.empty-state i {
    font-size: 3rem;
    color: #9ca3af;
}

.empty-state h4 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #374151;
    margin: 0;
}

.empty-state p {
    color: #6b7280;
    margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .section-title-input {
        margin-right: 0;
    }
    
    .section-actions {
        justify-content: space-between;
    }
    
    .field-row {
        flex-direction: column;
        gap: 1rem;
    }
    
    .field-inputs {
        grid-template-columns: 1fr;
    }
    
    .field-actions {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
}
</style>
@endpush

@push('scripts')
<script>
let sectionCounter = {{ count($template['sections'] ?? []) }};
let fieldCounters = {};

// Initialize field counters for existing sections
@if(!empty($template['sections']))
    @foreach($template['sections'] as $sectionIndex => $section)
        fieldCounters[{{ $sectionIndex }}] = {{ count($section['fields'] ?? []) }};
    @endforeach
@endif

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('templateEditForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
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
            throw new Error('Update failed');
        })
        .then(data => {
            // Show success message
            showToast('success', 'Template updated successfully');
            
            // Redirect to template show page
            setTimeout(() => {
                window.location.href = '{{ route("performance.templates.show", $template["id"]) }}';
            }, 1500);
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Failed to update template');
            
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
});

// Template Structure Management Functions
function addSection() {
    const templateStructure = document.getElementById('templateStructure');
    const emptySections = templateStructure.querySelector('.empty-sections');
    
    if (emptySections) {
        emptySections.remove();
    }
    
    const sectionHtml = `
        <div class="section-editor" data-section-index="${sectionCounter}">
            <div class="section-header">
                <div class="section-title-input">
                    <input type="text" 
                           name="sections[${sectionCounter}][title]" 
                           value="" 
                           placeholder="Section Title"
                           class="form-control">
                </div>
                <div class="section-actions">
                    <button type="button" class="btn btn-outline btn-sm" onclick="addFieldToSection(${sectionCounter})">
                        <i class="fas fa-plus"></i>
                        Add Field
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeSection(${sectionCounter})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            
            <div class="section-description-input">
                <textarea name="sections[${sectionCounter}][description]" 
                          placeholder="Section Description (optional)"
                          class="form-control"
                          rows="2"></textarea>
            </div>
            
            <div class="section-fields">
                <div class="empty-fields">
                    <p>No fields in this section. Click "Add Field" to get started.</p>
                </div>
            </div>
        </div>
    `;
    
    templateStructure.insertAdjacentHTML('beforeend', sectionHtml);
    fieldCounters[sectionCounter] = 0;
    sectionCounter++;
}

function addFieldToSection(sectionIndex) {
    const sectionEditor = document.querySelector(`[data-section-index="${sectionIndex}"]`);
    const sectionFields = sectionEditor.querySelector('.section-fields');
    const emptyFields = sectionFields.querySelector('.empty-fields');
    
    if (emptyFields) {
        emptyFields.remove();
    }
    
    const fieldIndex = fieldCounters[sectionIndex] || 0;
    const fieldHtml = `
        <div class="field-editor" data-field-index="${fieldIndex}">
            <div class="field-row">
                <div class="field-inputs">
                    <input type="text" 
                           name="sections[${sectionIndex}][fields][${fieldIndex}][label]" 
                           value="" 
                           placeholder="Field Label"
                           class="form-control">
                    <select name="sections[${sectionIndex}][fields][${fieldIndex}][type]" 
                            class="form-control" onchange="toggleFieldOptions(this)">
                        <option value="text">Text Input</option>
                        <option value="textarea">Text Area</option>
                        <option value="select">Dropdown</option>
                        <option value="radio">Radio Buttons</option>
                        <option value="checkbox">Checkboxes</option>
                        <option value="rating">Rating Scale</option>
                        <option value="date">Date Picker</option>
                        <option value="number">Number Input</option>
                    </select>
                    <input type="text" 
                           name="sections[${sectionIndex}][fields][${fieldIndex}][placeholder]" 
                           value="" 
                           placeholder="Placeholder text"
                           class="form-control">
                </div>
                <div class="field-actions">
                    <label class="field-required">
                        <input type="checkbox" 
                               name="sections[${sectionIndex}][fields][${fieldIndex}][required]" 
                               value="1">
                        Required
                    </label>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeField(${sectionIndex}, ${fieldIndex})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            
            <div class="field-options" style="display: none;">
                <label>Options (one per line):</label>
                <textarea name="sections[${sectionIndex}][fields][${fieldIndex}][options]" 
                          class="form-control"
                          rows="3"
                          placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
            </div>
        </div>
    `;
    
    sectionFields.insertAdjacentHTML('beforeend', fieldHtml);
    fieldCounters[sectionIndex] = (fieldCounters[sectionIndex] || 0) + 1;
}

function removeSection(sectionIndex) {
    if (confirm('Are you sure you want to remove this section? All fields in this section will be deleted.')) {
        const sectionEditor = document.querySelector(`[data-section-index="${sectionIndex}"]`);
        sectionEditor.remove();
        delete fieldCounters[sectionIndex];
    }
}

function removeField(sectionIndex, fieldIndex) {
    if (confirm('Are you sure you want to remove this field?')) {
        const fieldEditor = document.querySelector(`[data-section-index="${sectionIndex}"] [data-field-index="${fieldIndex}"]`);
        fieldEditor.remove();
        
        // Check if section is now empty
        const sectionFields = document.querySelector(`[data-section-index="${sectionIndex}"] .section-fields`);
        if (sectionFields.children.length === 0) {
            sectionFields.innerHTML = '<div class="empty-fields"><p>No fields in this section. Click "Add Field" to get started.</p></div>';
        }
    }
}

function toggleFieldOptions(selectElement) {
    const fieldEditor = selectElement.closest('.field-editor');
    const fieldOptions = fieldEditor.querySelector('.field-options');
    const fieldType = selectElement.value;
    
    if (['select', 'radio', 'checkbox'].includes(fieldType)) {
        fieldOptions.style.display = 'block';
    } else {
        fieldOptions.style.display = 'none';
    }
}

// Initialize field options visibility for existing fields
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('select[name*="[type]"]').forEach(select => {
        toggleFieldOptions(select);
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
