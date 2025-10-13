@extends('layouts.pms-app')

@section('title', 'Template Builder')

@section('page-title', 'Template Builder')
@section('page-subtitle', 'Build and customize performance evaluation templates')

@section('content')
<div class="page-actions">
    <button class="btn btn-success" onclick="saveTemplate()">
        <i class="fas fa-save"></i>
        Save Template
    </button>
</div>

<div class="template-builder">
    <!-- Template Settings Panel -->
    <div class="builder-sidebar">
        <div class="sidebar-section">
            <h3 class="section-title">Template Settings</h3>
            <form id="templateSettingsForm">
                <div class="form-group">
                    <label class="form-label">Template Name</label>
                    <input type="text" name="template_name" class="form-control" placeholder="e.g., Q1 Performance Review" value="{{ $template['name'] ?? '' }}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Brief description of this template">{{ $template['description'] ?? '' }}</textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Review Type</label>
                    <select name="review_type" class="form-control">
                        <option value="self">Self Review</option>
                        <option value="manager">Manager Review</option>
                        <option value="360">360 Degree Review</option>
                        <option value="peer">Peer Review</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Scoring Method</label>
                    <select name="scoring_method" class="form-control">
                        <option value="5-point">5-Point Scale</option>
                        <option value="10-point">10-Point Scale</option>
                        <option value="percentage">Percentage (0-100%)</option>
                        <option value="letter">Letter Grade (A-F)</option>
                    </select>
                </div>
            </form>
        </div>
        
        <div class="sidebar-section">
            <h3 class="section-title">Field Library</h3>
            <div class="field-library">
                <div class="field-category">
                    <h4 class="category-title">Basic Fields</h4>
                    <div class="field-items">
                        <div class="field-item" draggable="true" data-field-type="text">
                            <i class="fas fa-font"></i>
                            <span>Text Input</span>
                        </div>
                        <div class="field-item" draggable="true" data-field-type="textarea">
                            <i class="fas fa-align-left"></i>
                            <span>Text Area</span>
                        </div>
                        <div class="field-item" draggable="true" data-field-type="number">
                            <i class="fas fa-hashtag"></i>
                            <span>Number Input</span>
                        </div>
                        <div class="field-item" draggable="true" data-field-type="email">
                            <i class="fas fa-envelope"></i>
                            <span>Email Input</span>
                        </div>
                        <div class="field-item" draggable="true" data-field-type="date">
                            <i class="fas fa-calendar"></i>
                            <span>Date Picker</span>
                        </div>
                        <div class="field-item" draggable="true" data-field-type="select">
                            <i class="fas fa-list"></i>
                            <span>Dropdown</span>
                        </div>
                        <div class="field-item" draggable="true" data-field-type="radio">
                            <i class="fas fa-dot-circle"></i>
                            <span>Radio Buttons</span>
                        </div>
                        <div class="field-item" draggable="true" data-field-type="checkbox">
                            <i class="fas fa-check-square"></i>
                            <span>Checkbox</span>
                        </div>
                    </div>
                </div>
                
                <div class="field-category">
                    <h4 class="category-title">Rating & Assessment</h4>
                    <div class="field-items">
                        <div class="field-item" draggable="true" data-field-type="rating">
                            <i class="fas fa-star"></i>
                            <span>Star Rating</span>
                        </div>
                        <div class="field-item" draggable="true" data-field-type="scale">
                            <i class="fas fa-sliders-h"></i>
                            <span>Scale Rating</span>
                        </div>
                        <div class="field-item" draggable="true" data-field-type="yesno">
                            <i class="fas fa-toggle-on"></i>
                            <span>Yes/No</span>
                        </div>
                        <div class="field-item" draggable="true" data-field-type="score">
                            <i class="fas fa-percentage"></i>
                            <span>Score Input</span>
                        </div>
                    </div>
                </div>
                
                <div class="field-category">
                    <h4 class="category-title">KPI Fields</h4>
                    <div class="field-items">
                        @foreach($availableKPIs ?? [] as $kpi)
                        <div class="field-item" draggable="true" data-field-type="kpi" data-kpi-id="{{ $kpi['id'] }}">
                            <i class="fas fa-chart-line"></i>
                            <span>{{ $kpi['name'] }}</span>
                            <small class="field-category-label">{{ $kpi['category'] }}</small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Template Canvas -->
    <div class="builder-canvas">
        <div class="canvas-header">
            <h3 class="canvas-title">Template Layout</h3>
            <div class="canvas-actions">
                <button class="btn btn-outline btn-sm" onclick="clearCanvas()">
                    <i class="fas fa-trash"></i>
                    Clear All
                </button>
            </div>
        </div>
        
        <div class="canvas-content" id="templateCanvas">
            <div class="drop-zone" id="dropZone">
                <div class="drop-zone-placeholder">
                    <i class="fas fa-mouse-pointer"></i>
                    <p>Drag fields from the library to build your template</p>
                    <small>You can reorder fields by dragging them within the canvas</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Field Properties Panel -->
    <div class="properties-panel" id="propertiesPanel" style="display: none;">
        <div class="panel-header">
            <h3 class="panel-title">Field Properties</h3>
            <button class="panel-close" onclick="closePropertiesPanel()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="panel-content" id="propertiesPanelContent">
            <!-- Dynamic content based on selected field -->
        </div>
    </div>
</div>

<!-- Field Templates (Hidden) -->
<div id="fieldTemplates" style="display: none;">
    <div id="textFieldTemplate" class="field-template">
        <div class="canvas-field" data-field-type="text">
            <div class="field-header">
                <span class="field-label">Text Input Field</span>
                <div class="field-actions">
                    <button class="btn-icon" onclick="editField(this)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon" onclick="deleteField(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="field-preview">
                <input type="text" class="form-control" placeholder="Preview of text input" disabled>
            </div>
        </div>
    </div>
    
    <div id="textareaFieldTemplate" class="field-template">
        <div class="canvas-field" data-field-type="textarea">
            <div class="field-header">
                <span class="field-label">Text Area Field</span>
                <div class="field-actions">
                    <button class="btn-icon" onclick="editField(this)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon" onclick="deleteField(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="field-preview">
                <textarea class="form-control" rows="3" placeholder="Preview of text area" disabled></textarea>
            </div>
        </div>
    </div>
    
    <div id="ratingFieldTemplate" class="field-template">
        <div class="canvas-field" data-field-type="rating">
            <div class="field-header">
                <span class="field-label">Star Rating Field</span>
                <div class="field-actions">
                    <button class="btn-icon" onclick="editField(this)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon" onclick="deleteField(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="field-preview">
                <div class="rating-preview">
                    <span>1</span>
                    <div class="rating-stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <span>5</span>
                </div>
            </div>
        </div>
    </div>
    
    <div id="selectFieldTemplate" class="field-template">
        <div class="canvas-field" data-field-type="select">
            <div class="field-header">
                <span class="field-label">Dropdown Field</span>
                <div class="field-actions">
                    <button class="btn-icon" onclick="editField(this)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon" onclick="deleteField(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="field-preview">
                <select class="form-control" disabled>
                    <option>Select an option...</option>
                    <option>Option 1</option>
                    <option>Option 2</option>
                </select>
            </div>
        </div>
    </div>
    
    <div id="numberFieldTemplate" class="field-template">
        <div class="canvas-field" data-field-type="number">
            <div class="field-header">
                <span class="field-label">Number Input Field</span>
                <div class="field-actions">
                    <button class="btn-icon" onclick="editField(this)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon" onclick="deleteField(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="field-preview">
                <input type="number" class="form-control" placeholder="Enter a number" disabled>
            </div>
        </div>
    </div>
    
    <div id="scaleFieldTemplate" class="field-template">
        <div class="canvas-field" data-field-type="scale">
            <div class="field-header">
                <span class="field-label">Scale Rating Field</span>
                <div class="field-actions">
                    <button class="btn-icon" onclick="editField(this)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon" onclick="deleteField(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="field-preview">
                <input type="range" class="form-control" min="1" max="10" value="5" disabled>
                <div class="scale-labels">
                    <span>1</span>
                    <span>10</span>
                </div>
            </div>
        </div>
    </div>
    
    <div id="yesnoFieldTemplate" class="field-template">
        <div class="canvas-field" data-field-type="yesno">
            <div class="field-header">
                <span class="field-label">Yes/No Field</span>
                <div class="field-actions">
                    <button class="btn-icon" onclick="editField(this)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon" onclick="deleteField(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="field-preview">
                <div class="yesno-options">
                    <label><input type="radio" name="preview" disabled> Yes</label>
                    <label><input type="radio" name="preview" disabled> No</label>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.template-builder {
    display: grid;
    grid-template-columns: 300px 1fr 280px;
    gap: var(--spacing-lg);
    height: calc(100vh - 200px);
    min-height: 600px;
}

.builder-sidebar {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
    border: 1px solid var(--border-color);
    overflow-y: auto;
}

.sidebar-section {
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--border-color);
}

.sidebar-section:last-child {
    border-bottom: none;
}

.section-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 var(--spacing-md) 0;
}

.field-library {
    max-height: 400px;
    overflow-y: auto;
}

.field-category {
    margin-bottom: var(--spacing-lg);
}

.category-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-secondary);
    margin: 0 0 var(--spacing-sm) 0;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 0.25rem;
}

.field-items {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.field-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-sm);
    background: var(--bg-secondary);
    border-radius: var(--border-radius);
    cursor: grab;
    transition: var(--transition);
}

.field-item:hover {
    background: var(--primary-color);
    color: var(--white);
}

.field-item:active {
    cursor: grabbing;
}

.field-item[draggable="true"] {
    cursor: grab;
    user-select: none;
}

.field-item[draggable="true"]:hover {
    background: var(--primary-color);
    color: var(--white);
    transform: translateY(-1px);
    box-shadow: var(--shadow);
}

.field-category-label {
    font-size: 0.7rem;
    color: var(--text-muted);
    margin-left: auto;
}

.builder-canvas {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
    border: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
}

.canvas-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--border-color);
}

.canvas-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.canvas-content {
    flex: 1;
    padding: var(--spacing-lg);
    overflow-y: auto;
}

.drop-zone {
    min-height: 100%;
    border: 2px dashed var(--border-color);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    transition: var(--transition);
}

.drop-zone.drag-over {
    border-color: var(--primary-color);
    background-color: rgba(79, 70, 229, 0.05);
}

.drop-zone-placeholder {
    text-align: center;
    color: var(--text-muted);
    padding: 3rem;
}

.drop-zone-placeholder i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.canvas-field {
    background: var(--white);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    margin-bottom: var(--spacing-md);
    transition: var(--transition);
}

.canvas-field:hover {
    border-color: var(--primary-color);
    box-shadow: var(--shadow);
}

.canvas-field.selected {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
}

.field-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-md);
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
}

.field-label {
    font-weight: 600;
    color: var(--text-primary);
}

.field-actions {
    display: flex;
    gap: 0.25rem;
}

.btn-icon {
    padding: 0.25rem;
    border: none;
    background: none;
    color: var(--text-muted);
    cursor: pointer;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.btn-icon:hover {
    color: var(--text-primary);
    background: var(--white);
}

.field-preview {
    padding: var(--spacing-md);
}

.rating-preview {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.rating-stars {
    display: flex;
    gap: 0.125rem;
    color: var(--warning-color);
}

.properties-panel {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
    border: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--border-color);
}

.panel-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.panel-close {
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    font-size: 1.25rem;
    padding: 0.25rem;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.panel-close:hover {
    color: var(--text-primary);
    background: var(--bg-secondary);
}

.panel-content {
    flex: 1;
    padding: var(--spacing-lg);
    overflow-y: auto;
}

@media (max-width: 1200px) {
    .template-builder {
        grid-template-columns: 250px 1fr;
    }
    
    .properties-panel {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 90%;
        max-width: 400px;
        max-height: 80vh;
        z-index: var(--z-modal);
    }
}

@media (max-width: 768px) {
    .template-builder {
        grid-template-columns: 1fr;
        grid-template-rows: auto 1fr;
    }
    
    .builder-sidebar {
        max-height: 300px;
    }
}
</style>
@endpush

@push('scripts')
<script>
let draggedField = null;
let selectedField = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeDragAndDrop();
    setupCanvasInteractions();
});

function initializeDragAndDrop() {
    // Make field items draggable
    const fieldItems = document.querySelectorAll('.field-item');
    fieldItems.forEach(item => {
        item.setAttribute('draggable', 'true');
        item.addEventListener('dragstart', handleDragStart);
        item.addEventListener('dragend', handleDragEnd);
        
        // Prevent default drag behavior on images and icons
        const icons = item.querySelectorAll('i, img');
        icons.forEach(icon => {
            icon.addEventListener('dragstart', e => e.preventDefault());
        });
    });
    
    // Setup drop zone
    const dropZone = document.getElementById('dropZone');
    dropZone.addEventListener('dragover', handleDragOver);
    dropZone.addEventListener('drop', handleDrop);
    dropZone.addEventListener('dragleave', handleDragLeave);
    dropZone.addEventListener('dragenter', handleDragEnter);
}

function handleDragStart(e) {
    console.log('Drag started for:', e.target);
    
    // Get the field type from the dragged element
    const fieldType = e.target.getAttribute('data-field-type') || e.target.closest('[data-field-type]')?.getAttribute('data-field-type');
    const kpiId = e.target.getAttribute('data-kpi-id') || e.target.closest('[data-kpi-id]')?.getAttribute('data-kpi-id');
    
    if (!fieldType) {
        console.error('No field type found for dragged element');
        e.preventDefault();
        return;
    }
    
    draggedField = {
        type: fieldType,
        kpiId: kpiId,
        element: e.target
    };
    
    console.log('Dragged field:', draggedField);
    
    e.dataTransfer.effectAllowed = 'copy';
    e.dataTransfer.setData('text/plain', fieldType);
    
    // Add visual feedback
    e.target.style.opacity = '0.5';
}

function handleDragEnd(e) {
    // Reset visual feedback
    e.target.style.opacity = '';
}

function handleDragEnter(e) {
    e.preventDefault();
    e.currentTarget.classList.add('drag-over');
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
    e.currentTarget.classList.add('drag-over');
}

function handleDragLeave(e) {
    e.currentTarget.classList.remove('drag-over');
}

function handleDrop(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');
    
    console.log('Drop event triggered');
    console.log('Dragged field:', draggedField);
    
    if (draggedField) {
        addFieldToCanvas(draggedField);
        draggedField = null;
    } else {
        console.error('No dragged field found on drop');
    }
}

function addFieldToCanvas(fieldData) {
    console.log('Adding field to canvas:', fieldData.type);
    
    const placeholder = document.querySelector('.drop-zone-placeholder');
    if (placeholder) {
        placeholder.style.display = 'none';
    }
    
    const template = document.getElementById(fieldData.type + 'FieldTemplate');
    if (!template) {
        console.error('Template not found for field type:', fieldData.type);
        
        // Create a basic template if none exists
        createBasicFieldTemplate(fieldData);
        return;
    }
    
    const fieldElement = template.cloneNode(true);
    fieldElement.style.display = 'block';
    fieldElement.id = 'field_' + Date.now();
    fieldElement.removeAttribute('data-template-id');
    
    // Customize field based on type
    if (fieldData.type === 'kpi' && fieldData.kpiId) {
        const kpiElement = document.querySelector(`[data-kpi-id="${fieldData.kpiId}"] span`);
        if (kpiElement) {
            const kpiName = kpiElement.textContent;
            fieldElement.querySelector('.field-label').textContent = kpiName + ' (KPI)';
        }
    }
    
    // Add the field to the canvas
    const dropZone = document.getElementById('dropZone');
    dropZone.appendChild(fieldElement);
    
    // Make the new field interactive
    setupFieldInteractions(fieldElement.querySelector('.canvas-field'));
    
    console.log('Field added successfully');
}

function createBasicFieldTemplate(fieldData) {
    console.log('Creating basic template for:', fieldData.type);
    
    const fieldElement = document.createElement('div');
    fieldElement.className = 'canvas-field';
    fieldElement.setAttribute('data-field-type', fieldData.type);
    fieldElement.id = 'field_' + Date.now();
    
    fieldElement.innerHTML = `
        <div class="field-header">
            <span class="field-label">${fieldData.type.charAt(0).toUpperCase() + fieldData.type.slice(1)} Field</span>
            <div class="field-actions">
                <button class="btn-icon" onclick="editField(this)">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon" onclick="deleteField(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="field-preview">
            <input type="${fieldData.type === 'text' ? 'text' : fieldData.type}" class="form-control" placeholder="Preview of ${fieldData.type} field" disabled>
        </div>
    `;
    
    const dropZone = document.getElementById('dropZone');
    dropZone.appendChild(fieldElement);
    
    setupFieldInteractions(fieldElement);
}

function setupCanvasInteractions() {
    // Canvas click to deselect fields
    document.getElementById('dropZone').addEventListener('click', function(e) {
        if (e.target === this) {
            deselectAllFields();
        }
    });
}

function setupFieldInteractions(fieldElement) {
    // Field selection
    fieldElement.addEventListener('click', function(e) {
        e.stopPropagation();
        selectField(this);
    });
    
    // Make field sortable within canvas
    fieldElement.draggable = true;
    fieldElement.addEventListener('dragstart', function(e) {
        e.dataTransfer.effectAllowed = 'move';
        this.classList.add('dragging');
    });
    
    fieldElement.addEventListener('dragend', function(e) {
        this.classList.remove('dragging');
    });
}

function selectField(fieldElement) {
    deselectAllFields();
    fieldElement.classList.add('selected');
    selectedField = fieldElement;
    showPropertiesPanel(fieldElement);
}

function deselectAllFields() {
    document.querySelectorAll('.canvas-field.selected').forEach(field => {
        field.classList.remove('selected');
    });
    selectedField = null;
    closePropertiesPanel();
}

function editField(button) {
    const field = button.closest('.canvas-field');
    selectField(field);
}

function deleteField(button) {
    const field = button.closest('.canvas-field');
    if (confirm('Are you sure you want to delete this field?')) {
        field.remove();
        
        // Show placeholder if no fields left
        const remainingFields = document.querySelectorAll('.canvas-field');
        if (remainingFields.length === 0) {
            document.querySelector('.drop-zone-placeholder').style.display = 'block';
        }
    }
}

function showPropertiesPanel(fieldElement) {
    const panel = document.getElementById('propertiesPanel');
    const content = document.getElementById('propertiesPanelContent');
    
    // Generate properties form based on field type
    const fieldType = fieldElement.dataset.fieldType;
    let propertiesHTML = generatePropertiesForm(fieldType);
    
    content.innerHTML = propertiesHTML;
    panel.style.display = 'flex';
}

function generatePropertiesForm(fieldType) {
    let html = `
        <div class="form-group">
            <label class="form-label">Field Label</label>
            <input type="text" class="form-control" name="field_label" placeholder="Enter field label">
        </div>
        
        <div class="form-group">
            <label class="form-label">Help Text</label>
            <textarea class="form-control" name="help_text" rows="2" placeholder="Optional help text"></textarea>
        </div>
        
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="required" class="form-check-input">
                <span class="checkmark"></span>
                Required Field
            </label>
        </div>
    `;
    
    // Add field-specific properties
    switch (fieldType) {
        case 'rating':
            html += `
                <div class="form-group">
                    <label class="form-label">Rating Scale</label>
                    <select class="form-control" name="rating_scale">
                        <option value="5">1-5 Scale</option>
                        <option value="10">1-10 Scale</option>
                    </select>
                </div>
            `;
            break;
        case 'scale':
            html += `
                <div class="form-group">
                    <label class="form-label">Scale Range</label>
                    <div class="range-inputs">
                        <input type="number" class="form-control" name="scale_min" value="1" placeholder="Min">
                        <span>to</span>
                        <input type="number" class="form-control" name="scale_max" value="10" placeholder="Max">
                    </div>
                </div>
            `;
            break;
        case 'select':
        case 'radio':
            html += `
                <div class="form-group">
                    <label class="form-label">Options (one per line)</label>
                    <textarea class="form-control" name="options" rows="4" placeholder="Option 1\nOption 2\nOption 3"></textarea>
                </div>
            `;
            break;
        case 'number':
        case 'score':
            html += `
                <div class="form-group">
                    <label class="form-label">Number Range</label>
                    <div class="range-inputs">
                        <input type="number" class="form-control" name="min_value" placeholder="Min">
                        <span>to</span>
                        <input type="number" class="form-control" name="max_value" placeholder="Max">
                    </div>
                </div>
            `;
            break;
    }
    
    html += `
        <div class="form-group">
            <button type="button" class="btn btn-primary" onclick="applyFieldProperties()">
                <i class="fas fa-check"></i>
                Apply Changes
            </button>
            <button type="button" class="btn btn-outline" onclick="closePropertiesPanel()">
                Cancel
            </button>
        </div>
    `;
    
    return html;
}

function applyFieldProperties() {
    if (!selectedField) return;
    
    const panel = document.getElementById('propertiesPanelContent');
    
    // Update field label
    const labelInput = panel.querySelector('[name="field_label"]');
    if (labelInput && labelInput.value.trim()) {
        selectedField.querySelector('.field-label').textContent = labelInput.value.trim();
    }
    
    // Update help text (add if provided)
    const helpInput = panel.querySelector('[name="help_text"]');
    if (helpInput && helpInput.value.trim()) {
        let helpElement = selectedField.querySelector('.field-help');
        if (!helpElement) {
            helpElement = document.createElement('small');
            helpElement.className = 'field-help';
            selectedField.querySelector('.field-preview').appendChild(helpElement);
        }
        helpElement.textContent = helpInput.value.trim();
    }
    
    // Update required status
    const requiredInput = panel.querySelector('[name="required"]');
    const labelElement = selectedField.querySelector('.field-label');
    if (requiredInput && requiredInput.checked) {
        if (!labelElement.querySelector('.required-asterisk')) {
            const asterisk = document.createElement('span');
            asterisk.className = 'required-asterisk';
            asterisk.textContent = ' *';
            asterisk.style.color = 'var(--danger-color)';
            labelElement.appendChild(asterisk);
        }
    } else {
        const asterisk = labelElement.querySelector('.required-asterisk');
        if (asterisk) {
            asterisk.remove();
        }
    }
    
    if (window.PMS && window.PMS.notifications) {
        window.PMS.notifications.success('Field properties updated!');
    } else {
        alert('Field properties updated!');
    }
    
    // Close properties panel
    closePropertiesPanel();
}

function closePropertiesPanel() {
    document.getElementById('propertiesPanel').style.display = 'none';
}

function clearCanvas() {
    if (confirm('Are you sure you want to clear all fields from the canvas?')) {
        document.querySelectorAll('.canvas-field').forEach(field => field.remove());
        document.querySelector('.drop-zone-placeholder').style.display = 'block';
        closePropertiesPanel();
    }
}


function saveTemplate() {
    const templateName = document.querySelector('[name="template_name"]').value;
    
    if (!templateName) {
        if (window.PMS && window.PMS.notifications) {
            window.PMS.notifications.error('Please enter a template name');
        }
        return;
    }
    
    const fields = document.querySelectorAll('.canvas-field');
    if (fields.length === 0) {
        if (window.PMS && window.PMS.notifications) {
            window.PMS.notifications.error('Please add at least one field to the template');
        }
        return;
    }
    
    if (window.PMS && window.PMS.notifications) {
        window.PMS.notifications.success('Template saved successfully!');
    }
    
    // In a real implementation, collect all form data and field configurations
    // and submit to the server
    
    setTimeout(() => {
        window.location.href = '{{ route("performance.templates.index") }}';
    }, 1500);
}
</script>
@endpush
