@extends('layouts.pms-app')

@section('title', 'Template Details')

@section('page-title', 'Template Details')
@section('page-subtitle', 'View and manage template information')

@section('content')
<div class="page-actions">
    <a href="{{ route('performance.templates.index') }}" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i>
        Back to Templates
    </a>
    <a href="{{ route('performance.templates.edit', $template['id']) }}" class="btn btn-outline">
        <i class="fas fa-edit"></i>
        Edit Template
    </a>
    <a href="{{ route('performance.templates.builder', $template['id']) }}" class="btn btn-primary">
        <i class="fas fa-tools"></i>
        Open Builder
    </a>
</div>

<!-- Template Information Card -->
<div class="modern-card">
    <div class="card-header-modern">
        <div class="header-content">
            <div class="template-info">
                <h3 class="template-title-modern">{{ $template['name'] }}</h3>
                <p class="template-description-modern">{{ $template['description'] }}</p>
                <div class="template-meta-modern">
                    <span class="meta-badge">
                        <i class="fas fa-tag"></i>
                        {{ ucfirst($template['category'] ?? 'performance') }} Template
                    </span>
                    <span class="meta-badge">
                        <i class="fas fa-clock"></i>
                        Updated {{ \Carbon\Carbon::parse($template['updated_at'] ?? $template['created_at'])->diffForHumans() }}
                    </span>
                </div>
            </div>
            <div class="header-actions">
                <span class="status-badge-modern status-{{ $template['status'] }}">
                    <i class="fas fa-{{ $template['status'] === 'active' ? 'check-circle' : 'pause-circle' }}"></i>
                    {{ ucfirst($template['status']) }}
                </span>
                <div class="dropdown-modern">
                    <button class="action-btn dropdown-toggle" title="More Actions">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu-modern">
                        <a href="{{ route('performance.templates.duplicate', $template['id']) }}" class="dropdown-item-modern">
                            <i class="fas fa-copy"></i>
                            <span>Duplicate Template</span>
                        </a>
                        <div class="dropdown-divider-modern"></div>
                        @if($template['status'] === 'active')
                            <a href="{{ route('performance.templates.unpublish', $template['id']) }}" class="dropdown-item-modern">
                                <i class="fas fa-eye-slash"></i>
                                <span>Unpublish</span>
                            </a>
                        @else
                            <a href="{{ route('performance.templates.publish', $template['id']) }}" class="dropdown-item-modern">
                                <i class="fas fa-eye"></i>
                                <span>Publish</span>
                            </a>
                        @endif
                        <div class="dropdown-divider-modern"></div>
                        <a href="#" class="dropdown-item-modern text-danger" onclick="deleteTemplate({{ $template['id'] }})">
                            <i class="fas fa-trash"></i>
                            <span>Delete Template</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Modern Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card stat-card-primary">
                <div class="stat-card-header">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+12%</span>
                    </div>
                </div>
                <div class="stat-card-content">
                    <div class="stat-number">{{ $template['usage_count'] ?? 0 }}</div>
                    <div class="stat-label">Times Used</div>
                    <div class="stat-description">Total submissions</div>
                </div>
            </div>
            
            <div class="stat-card stat-card-success">
                <div class="stat-card-header">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-trend neutral">
                        <i class="fas fa-minus"></i>
                    </div>
                </div>
                <div class="stat-card-content">
                    <div class="stat-number">{{ \Carbon\Carbon::parse($template['created_at'])->format('M d') }}</div>
                    <div class="stat-label">Created</div>
                    <div class="stat-description">{{ \Carbon\Carbon::parse($template['created_at'])->format('F Y') }}</div>
                </div>
            </div>
            
            <div class="stat-card stat-card-info">
                <div class="stat-card-header">
                    <div class="stat-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+2</span>
                    </div>
                </div>
                <div class="stat-card-content">
                    <div class="stat-number">{{ $template['department_count'] ?? 0 }}</div>
                    <div class="stat-label">Departments</div>
                    <div class="stat-description">Active departments</div>
                </div>
            </div>
            
            <div class="stat-card stat-card-warning">
                <div class="stat-card-header">
                    <div class="stat-icon">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+5</span>
                    </div>
                </div>
                <div class="stat-card-content">
                    <div class="stat-number">{{ $template['field_count'] ?? 0 }}</div>
                    <div class="stat-label">Fields</div>
                    <div class="stat-description">Form elements</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template Sections -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Template Structure -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Template Structure</h3>
            <a href="{{ route('performance.templates.builder', $template['id']) }}" class="btn btn-outline btn-sm">
                <i class="fas fa-edit"></i>
                Edit Structure
            </a>
        </div>
        <div class="card-body">
            <div class="template-sections">
                @if(isset($template['sections']) && count($template['sections']) > 0)
                    @foreach($template['sections'] as $section)
                        <div class="section-item">
                            <div class="section-header">
                                <h4 class="section-title">{{ $section['title'] }}</h4>
                                <span class="section-field-count">{{ count($section['fields'] ?? []) }} fields</span>
                            </div>
                            @if(!empty($section['description']))
                                <p class="section-description">{{ $section['description'] }}</p>
                            @endif
                            
                            @if(isset($section['fields']) && count($section['fields']) > 0)
                                <div class="section-fields">
                                    @foreach($section['fields'] as $field)
                                        <div class="field-item-preview">
                                            <i class="fas fa-{{ $field['icon'] ?? 'circle' }} field-icon"></i>
                                            <span class="field-name">{{ $field['label'] ?? $field['type'] }}</span>
                                            <span class="field-type">{{ ucfirst($field['type']) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-state">
                                    <i class="fas fa-plus-circle"></i>
                                    <p>No fields in this section</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <p>No sections defined</p>
                        <a href="{{ route('performance.templates.builder', $template['id']) }}" class="btn btn-primary btn-sm">
                            Add Sections
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Template Usage & Analytics -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Usage & Analytics</h3>
        </div>
        <div class="card-body">
            <div class="analytics-content">
                <!-- Recent Usage Chart -->
                <div class="chart-container">
                    <canvas id="usageChart"></canvas>
                </div>
                
                <!-- Recent Activity -->
                <div class="recent-activity">
                    <h4 class="activity-title">Recent Activity</h4>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon bg-success">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Template used for review</div>
                                <div class="activity-subtitle">John Doe - 2 hours ago</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon bg-info">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Template updated</div>
                                <div class="activity-subtitle">Admin - 1 day ago</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon bg-warning">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Template published</div>
                                <div class="activity-subtitle">Admin - 3 days ago</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template Settings -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Template Settings</h3>
    </div>
    <div class="card-body">
        <div class="settings-grid">
            <div class="setting-item">
                <div class="setting-label">Template Name</div>
                <div class="setting-value">{{ $template['name'] }}</div>
            </div>
            
            <div class="setting-item">
                <div class="setting-label">Description</div>
                <div class="setting-value">{{ $template['description'] }}</div>
            </div>
            
            <div class="setting-item">
                <div class="setting-label">Status</div>
                <div class="setting-value">
                    <span class="status-badge status-{{ $template['status'] }}">
                        {{ ucfirst($template['status']) }}
                    </span>
                </div>
            </div>
            
            <div class="setting-item">
                <div class="setting-label">Created</div>
                <div class="setting-value">{{ \Carbon\Carbon::parse($template['created_at'])->format('M d, Y') }}</div>
            </div>
            
            <div class="setting-item">
                <div class="setting-label">Last Modified</div>
                <div class="setting-value">{{ \Carbon\Carbon::parse($template['updated_at'] ?? $template['created_at'])->format('M d, Y') }}</div>
            </div>
            
            <div class="setting-item">
                <div class="setting-label">Usage Count</div>
                <div class="setting-value">{{ $template['usage_count'] ?? 0 }} times</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Modern Card Design */
.modern-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    overflow: hidden;
    margin-bottom: 2rem;
}

.card-header-modern {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 2rem;
    border-bottom: 1px solid #e2e8f0;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 2rem;
}

.template-info {
    flex: 1;
}

.template-title-modern {
    font-size: 1.875rem;
    font-weight: 800;
    color: #1e293b;
    margin: 0 0 0.5rem 0;
    line-height: 1.2;
    letter-spacing: -0.025em;
}

.template-description-modern {
    font-size: 1rem;
    color: #64748b;
    margin: 0 0 1rem 0;
    line-height: 1.6;
}

.template-meta-modern {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.meta-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: rgba(59, 130, 246, 0.1);
    color: #1d4ed8;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.status-badge-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-badge-modern.status-active {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
}

.status-badge-modern.status-inactive {
    background: linear-gradient(135deg, #6b7280, #4b5563);
    color: white;
    box-shadow: 0 4px 14px rgba(107, 114, 128, 0.4);
}

.dropdown-modern {
    position: relative;
}

.action-btn {
    width: 2.5rem;
    height: 2.5rem;
    border: 1px solid #d1d5db;
    background: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s ease;
}

.action-btn:hover {
    background: #f9fafb;
    border-color: #9ca3af;
    color: #374151;
}

.dropdown-menu-modern {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    min-width: 200px;
    z-index: 50;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s ease;
}

.dropdown-menu-modern.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-item-modern {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #374151;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: background-color 0.2s ease;
}

.dropdown-item-modern:hover {
    background: #f9fafb;
}

.dropdown-item-modern.text-danger {
    color: #dc2626;
}

.dropdown-item-modern.text-danger:hover {
    background: #fef2f2;
}

.dropdown-divider-modern {
    height: 1px;
    background: #e5e7eb;
    margin: 0.5rem 0;
}

/* Modern Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    border-color: #cbd5e1;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #3b82f6, #1d4ed8);
    border-radius: 16px 16px 0 0;
}

.stat-card-primary::before {
    background: linear-gradient(90deg, #3b82f6, #1d4ed8);
}

.stat-card-success::before {
    background: linear-gradient(90deg, #10b981, #059669);
}

.stat-card-info::before {
    background: linear-gradient(90deg, #06b6d4, #0891b2);
}

.stat-card-warning::before {
    background: linear-gradient(90deg, #f59e0b, #d97706);
}

.stat-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.stat-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    position: relative;
    overflow: hidden;
}

.stat-card-primary .stat-icon {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4);
}

.stat-card-success .stat-icon {
    background: linear-gradient(135deg, #10b981, #059669);
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
}

.stat-card-info .stat-icon {
    background: linear-gradient(135deg, #06b6d4, #0891b2);
    box-shadow: 0 4px 14px rgba(6, 182, 212, 0.4);
}

.stat-card-warning .stat-icon {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.4);
}

.stat-trend {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}

.stat-trend.positive {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
}

.stat-trend.negative {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
}

.stat-trend.neutral {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
}

.stat-card-content {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.stat-number {
    font-size: 2rem;
    font-weight: 800;
    color: #1e293b;
    line-height: 1;
    letter-spacing: -0.025em;
}

.stat-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.stat-description {
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 500;
}

.template-sections {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.section-item {
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: 1rem;
    background: var(--white);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.section-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.section-field-count {
    font-size: 0.75rem;
    color: var(--text-muted);
    background: var(--bg-secondary);
    padding: 0.25rem 0.5rem;
    border-radius: var(--border-radius-sm);
}

.section-description {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0 0 1rem 0;
}

.section-fields {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.field-item-preview {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    background: var(--bg-secondary);
    border-radius: var(--border-radius-sm);
    font-size: 0.875rem;
}

.field-icon {
    color: var(--primary-color);
    width: 1rem;
}

.field-name {
    flex: 1;
    color: var(--text-primary);
}

.field-type {
    font-size: 0.75rem;
    color: var(--text-muted);
    background: var(--white);
    padding: 0.125rem 0.375rem;
    border-radius: var(--border-radius-sm);
}

.empty-state {
    text-align: center;
    padding: 2rem;
    color: var(--text-muted);
}

.empty-state i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    opacity: 0.5;
}

.chart-container {
    height: 200px;
    margin-bottom: 1.5rem;
}

.recent-activity {
    margin-top: 1.5rem;
}

.activity-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 1rem 0;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem;
    border-radius: var(--border-radius);
    transition: background-color 0.2s ease;
}

.activity-item:hover {
    background: var(--bg-secondary);
}

.activity-icon {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.75rem;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-primary);
    margin: 0 0 0.125rem 0;
}

.activity-subtitle {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.setting-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.setting-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-secondary);
}

.setting-value {
    font-size: 0.875rem;
    color: var(--text-primary);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-active {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
}

.status-inactive {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
}

.status-draft {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }
}

@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .template-title-modern {
        font-size: 1.5rem;
    }
    
    .template-meta-modern {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .header-actions {
        align-self: stretch;
        justify-content: space-between;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .stat-card {
        padding: 1.25rem;
    }
    
    .stat-number {
        font-size: 1.75rem;
    }
    
    .stat-icon {
        width: 2.5rem;
        height: 2.5rem;
        font-size: 1rem;
    }
    
    .settings-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .card-header-modern {
        padding: 1.5rem;
    }
    
    .template-title-modern {
        font-size: 1.25rem;
    }
    
    .template-description-modern {
        font-size: 0.875rem;
    }
    
    .meta-badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    
    .status-badge-modern {
        font-size: 0.75rem;
        padding: 0.5rem 1rem;
    }
    
    .stat-card {
        padding: 1rem;
    }
    
    .stat-card-header {
        margin-bottom: 0.75rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .stat-label {
        font-size: 0.75rem;
    }
    
    .stat-description {
        font-size: 0.6875rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modern dropdown functionality
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Close other dropdowns
            document.querySelectorAll('.dropdown-menu-modern.show').forEach(menu => {
                if (menu !== this.nextElementSibling) {
                    menu.classList.remove('show');
                }
            });
            
            // Toggle current dropdown
            const menu = this.nextElementSibling;
            menu.classList.toggle('show');
        });
    });
    
    // Close dropdowns on outside click
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu-modern.show').forEach(menu => {
            menu.classList.remove('show');
        });
    });

    // Initialize usage chart
    const ctx = document.getElementById('usageChart');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Template Usage',
                    data: [12, 19, 3, 5, 2, 3],
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6'
                        }
                    }
                }
            }
        });
    }
});

function deleteTemplate(templateId) {
    if (confirm('Are you sure you want to delete this template? This action cannot be undone.')) {
        // Implement delete functionality
        fetch(`/performance/templates/${templateId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                window.location.href = '{{ route("performance.templates.index") }}';
            } else {
                alert('Failed to delete template');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the template');
        });
    }
}
</script>
@endpush
