@extends('layouts.pms-app')

@section('title', 'Performance Templates')

@section('page-title', 'Performance Templates')
@section('page-subtitle', 'Manage performance review templates and evaluation forms')

@section('content')
<div class="page-actions">
    <a href="{{ route('performance.templates.builder') }}" class="btn btn-primary">
        <i class="fas fa-tools"></i>
        Template Builder
    </a>
    <a href="{{ route('performance.templates.create') }}" class="btn btn-outline">
        <i class="fas fa-plus"></i>
        Create Template
    </a>
</div>

<!-- Templates Summary -->
<div class="dashboard-grid stats">
    @php
        $totalTemplates = count($templates ?? []);
        $activeTemplates = collect($templates ?? [])->where('status', 'active')->count();
        $totalUsage = collect($templates ?? [])->sum('usage_count');
    @endphp
    
    <div class="stat-card">
        <span class="stat-number">{{ $totalTemplates }}</span>
        <span class="stat-label">Total Templates</span>
        <div class="stat-change positive">
            <i class="fas fa-file-alt"></i>
            All templates
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">{{ $activeTemplates }}</span>
        <span class="stat-label">Active Templates</span>
        <div class="stat-change positive">
            <i class="fas fa-check-circle"></i>
            Currently used
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">{{ $totalUsage }}</span>
        <span class="stat-label">Total Usage</span>
        <div class="stat-change positive">
            <i class="fas fa-users"></i>
            Times used
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">{{ $totalTemplates > 0 ? number_format($totalUsage / $totalTemplates, 1) : '0' }}</span>
        <span class="stat-label">Avg Usage</span>
        <div class="stat-change positive">
            <i class="fas fa-chart-bar"></i>
            Per template
        </div>
    </div>
</div>

<!-- Templates Grid -->
<div class="templates-container">
    @if(!empty($templates) && count($templates) > 0)
        <div class="templates-grid">
            @foreach($templates as $template)
            <div class="template-card {{ $template['status'] }}">
                <div class="template-header">
                    <h3 class="template-title">{{ $template['name'] }}</h3>
                    <span class="template-status {{ $template['status'] }}">
                        @if($template['status'] == 'active')
                            <i class="fas fa-check-circle"></i>
                        @else
                            <i class="fas fa-pause-circle"></i>
                        @endif
                        {{ ucfirst($template['status']) }}
                    </span>
                </div>
                
                <div class="template-description">
                    {{ $template['description'] }}
                </div>
                
                <div class="template-meta">
                    <div class="meta-item">
                        <i class="fas fa-calendar"></i>
                        Created: {{ \Carbon\Carbon::parse($template['created_at'])->format('M d, Y') }}
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-users"></i>
                        Used {{ $template['usage_count'] }} times
                    </div>
                </div>
                
                <div class="template-actions">
                    <a href="{{ route('performance.templates.show', $template['id']) }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-eye"></i>
                        View
                    </a>
                    <a href="{{ route('performance.templates.edit', $template['id']) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i>
                        Edit
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline btn-sm dropdown-toggle">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a href="{{ route('performance.templates.builder', $template['id']) }}" class="dropdown-item">
                                <i class="fas fa-tools"></i>
                                Template Builder
                            </a>
                            <a href="#" onclick="duplicateTemplate({{ $template['id'] }})" class="dropdown-item">
                                <i class="fas fa-copy"></i>
                                Duplicate
                            </a>
                            @if($template['status'] == 'active')
                            <a href="#" onclick="deactivateTemplate({{ $template['id'] }})" class="dropdown-item">
                                <i class="fas fa-pause"></i>
                                Deactivate
                            </a>
                            @else
                            <a href="#" onclick="activateTemplate({{ $template['id'] }})" class="dropdown-item">
                                <i class="fas fa-play"></i>
                                Activate
                            </a>
                            @endif
                            <div class="dropdown-divider"></div>
                            <a href="#" onclick="deleteTemplate({{ $template['id'] }})" class="dropdown-item text-danger">
                                <i class="fas fa-trash"></i>
                                Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="empty-state-title">No Templates Yet</div>
            <div class="empty-state-description">
                Create your first performance template to get started with evaluations and reviews.
            </div>
            <div class="empty-state-actions">
                <a href="{{ route('performance.templates.builder') }}" class="btn btn-primary">
                    <i class="fas fa-tools"></i>
                    Use Template Builder
                </a>
                <a href="{{ route('performance.templates.create') }}" class="btn btn-outline">
                    <i class="fas fa-plus"></i>
                    Create Template
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.templates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
}

.template-card {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
    border: 1px solid var(--border-color);
    padding: var(--spacing-lg);
    transition: var(--transition);
    position: relative;
}

.template-card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.template-card.active {
    border-left: 4px solid var(--success-color);
}

.template-card.inactive {
    border-left: 4px solid var(--text-muted);
}

.template-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--spacing-md);
}

.template-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
    flex: 1;
    margin-right: var(--spacing-sm);
}

.template-status {
    padding: 0.25rem 0.5rem;
    border-radius: var(--border-radius);
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.template-status.active {
    background-color: var(--success-color);
    color: var(--white);
}

.template-status.inactive {
    background-color: var(--text-muted);
    color: var(--white);
}

.template-description {
    color: var(--text-secondary);
    margin-bottom: var(--spacing-lg);
    line-height: 1.5;
}

.template-meta {
    margin-bottom: var(--spacing-lg);
}

.meta-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    color: var(--text-muted);
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.template-actions {
    display: flex;
    gap: 0.25rem;
    align-items: center;
    padding-top: var(--spacing-md);
    border-top: 1px solid var(--border-color);
}

.dropdown {
    position: relative;
    margin-left: auto;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: var(--white);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
    min-width: 150px;
    z-index: var(--z-dropdown);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: var(--transition);
}

.dropdown-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-sm) var(--spacing-md);
    color: var(--text-primary);
    text-decoration: none;
    font-size: 0.875rem;
    transition: var(--transition);
}

.dropdown-item:hover {
    background-color: var(--bg-secondary);
}

.dropdown-item.text-danger {
    color: var(--danger-color);
}

.dropdown-divider {
    height: 1px;
    background-color: var(--border-color);
    margin: var(--spacing-xs) 0;
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

.empty-state-actions {
    display: flex;
    gap: var(--spacing-sm);
    justify-content: center;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .templates-grid {
        grid-template-columns: 1fr;
    }
    
    .template-header {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-sm);
    }
    
    .template-actions {
        flex-wrap: wrap;
    }
    
    .empty-state-actions {
        flex-direction: column;
        align-items: center;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Close other dropdowns
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
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
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    });
});

function duplicateTemplate(templateId) {
    if (window.PMS && window.PMS.notifications) {
        window.PMS.notifications.info('Duplicating template...');
    }
    
    // In a real implementation, make API call to duplicate
    setTimeout(() => {
        if (window.PMS && window.PMS.notifications) {
            window.PMS.notifications.success('Template duplicated successfully!');
        }
        window.location.reload();
    }, 1000);
}

function activateTemplate(templateId) {
    if (window.PMS && window.PMS.notifications) {
        window.PMS.notifications.info('Activating template...');
    }
    
    // In a real implementation, make API call to activate
    setTimeout(() => {
        if (window.PMS && window.PMS.notifications) {
            window.PMS.notifications.success('Template activated successfully!');
        }
        window.location.reload();
    }, 1000);
}

function deactivateTemplate(templateId) {
    if (window.PMS && window.PMS.notifications) {
        window.PMS.notifications.info('Deactivating template...');
    }
    
    // In a real implementation, make API call to deactivate
    setTimeout(() => {
        if (window.PMS && window.PMS.notifications) {
            window.PMS.notifications.success('Template deactivated successfully!');
        }
        window.location.reload();
    }, 1000);
}

function deleteTemplate(templateId) {
    if (window.PMS && window.PMS.modal) {
        const confirmed = window.PMS.modal.confirm(
            'Are you sure you want to delete this template? This action cannot be undone.',
            'Delete Template'
        );
        
        confirmed.then(result => {
            if (result) {
                // In a real implementation, make API call to delete
                window.PMS.notifications.success('Template deleted successfully!');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        });
    } else {
        const confirmed = confirm('Are you sure you want to delete this template? This action cannot be undone.');
        if (confirmed) {
            // In a real implementation, make API call to delete
            window.location.reload();
        }
    }
}
</script>
@endpush
