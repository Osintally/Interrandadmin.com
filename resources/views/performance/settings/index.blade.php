@extends('layouts.pms-app')

@section('title', 'Performance Settings')

@section('page-title', 'Performance Settings')
@section('page-subtitle', 'Configure performance management system settings and preferences')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Performance Settings</h1>
        <p class="page-subtitle">Customize the performance management system to fit your organization's needs</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-outline" onclick="resetSettings()">
            <i class="fas fa-undo"></i>
            Reset to Defaults
        </button>
        <button class="btn btn-success" onclick="saveSettings()">
            <i class="fas fa-save"></i>
            Save Changes
        </button>
    </div>
</div>

<form id="settingsForm">
    @csrf
    
    <!-- Performance Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Performance Review Settings</h3>
            <small class="card-subtitle">Configure how performance reviews are conducted</small>
        </div>
        <div class="card-body">
            <div class="settings-grid">
                <div class="setting-item">
                    <label class="setting-label">Review Frequency</label>
                    <select name="review_frequency" class="form-control">
                        <option value="monthly" {{ ($settings['performance']['review_frequency'] ?? '') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarterly" {{ ($settings['performance']['review_frequency'] ?? '') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                        <option value="semi-annual" {{ ($settings['performance']['review_frequency'] ?? '') == 'semi-annual' ? 'selected' : '' }}>Semi-Annual</option>
                        <option value="annual" {{ ($settings['performance']['review_frequency'] ?? '') == 'annual' ? 'selected' : '' }}>Annual</option>
                    </select>
                    <small class="setting-help">How often performance reviews should be conducted</small>
                </div>
                
                <div class="setting-item">
                    <label class="setting-label">Auto Notifications</label>
                    <label class="toggle-switch">
                        <input type="checkbox" name="auto_notifications" {{ ($settings['performance']['auto_notifications'] ?? false) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <small class="setting-help">Automatically send review reminders and notifications</small>
                </div>
                
                <div class="setting-item">
                    <label class="setting-label">Require Manager Approval</label>
                    <label class="toggle-switch">
                        <input type="checkbox" name="require_manager_approval" {{ ($settings['performance']['require_manager_approval'] ?? false) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <small class="setting-help">Reviews must be approved by a manager before submission</small>
                </div>
                
                <div class="setting-item">
                    <label class="setting-label">Allow Self Review</label>
                    <label class="toggle-switch">
                        <input type="checkbox" name="allow_self_review" {{ ($settings['performance']['allow_self_review'] ?? false) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <small class="setting-help">Allow employees to conduct self-assessments</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scoring Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Scoring & Evaluation</h3>
            <small class="card-subtitle">Configure how performance is scored and evaluated</small>
        </div>
        <div class="card-body">
            <div class="settings-grid">
                <div class="setting-item">
                    <label class="setting-label">Scale Type</label>
                    <select name="scale_type" class="form-control">
                        <option value="1-5" {{ ($settings['scoring']['scale_type'] ?? '') == '1-5' ? 'selected' : '' }}>1-5 Scale</option>
                        <option value="1-10" {{ ($settings['scoring']['scale_type'] ?? '') == '1-10' ? 'selected' : '' }}>1-10 Scale</option>
                        <option value="percentage" {{ ($settings['scoring']['scale_type'] ?? '') == 'percentage' ? 'selected' : '' }}>Percentage (0-100%)</option>
                        <option value="letter" {{ ($settings['scoring']['scale_type'] ?? '') == 'letter' ? 'selected' : '' }}>Letter Grade (A-F)</option>
                    </select>
                    <small class="setting-help">Default scoring scale for performance evaluations</small>
                </div>
                
                <div class="setting-item">
                    <label class="setting-label">Weighted Scoring</label>
                    <label class="toggle-switch">
                        <input type="checkbox" name="weighted_scoring" {{ ($settings['scoring']['weighted_scoring'] ?? false) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <small class="setting-help">Use weighted scores based on KPI importance</small>
                </div>
                
                <div class="setting-item">
                    <label class="setting-label">Auto Calculate Scores</label>
                    <label class="toggle-switch">
                        <input type="checkbox" name="auto_calculate" {{ ($settings['scoring']['auto_calculate'] ?? false) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <small class="setting-help">Automatically calculate overall scores from individual ratings</small>
                </div>
                
                <div class="setting-item">
                    <label class="setting-label">Minimum Score for Pass</label>
                    <input type="number" name="minimum_pass_score" class="form-control" min="0" max="100" value="70" placeholder="70">
                    <small class="setting-help">Minimum score required to pass performance review</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Notification Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Notifications & Reminders</h3>
            <small class="card-subtitle">Configure notification preferences and timing</small>
        </div>
        <div class="card-body">
            <div class="settings-grid">
                <div class="setting-item">
                    <label class="setting-label">Email Reminders</label>
                    <label class="toggle-switch">
                        <input type="checkbox" name="email_reminders" {{ ($settings['notifications']['email_reminders'] ?? false) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <small class="setting-help">Send email reminders for pending reviews</small>
                </div>
                
                <div class="setting-item">
                    <label class="setting-label">Deadline Alerts</label>
                    <label class="toggle-switch">
                        <input type="checkbox" name="deadline_alerts" {{ ($settings['notifications']['deadline_alerts'] ?? false) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <small class="setting-help">Alert users about approaching deadlines</small>
                </div>
                
                <div class="setting-item">
                    <label class="setting-label">Performance Updates</label>
                    <label class="toggle-switch">
                        <input type="checkbox" name="performance_updates" {{ ($settings['notifications']['performance_updates'] ?? false) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <small class="setting-help">Notify about performance score updates</small>
                </div>
                
                <div class="setting-item">
                    <label class="setting-label">Reminder Frequency</label>
                    <select name="reminder_frequency" class="form-control">
                        <option value="daily">Daily</option>
                        <option value="weekly" selected>Weekly</option>
                        <option value="bi-weekly">Bi-weekly</option>
                    </select>
                    <small class="setting-help">How often to send reminder notifications</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Data & Privacy Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data & Privacy</h3>
            <small class="card-subtitle">Configure data retention and privacy settings</small>
        </div>
        <div class="card-body">
            <div class="settings-grid">
                <div class="setting-item">
                    <label class="setting-label">Data Retention Period</label>
                    <select name="data_retention" class="form-control">
                        <option value="1year">1 Year</option>
                        <option value="2years">2 Years</option>
                        <option value="3years" selected>3 Years</option>
                        <option value="5years">5 Years</option>
                        <option value="indefinite">Indefinite</option>
                    </select>
                    <small class="setting-help">How long to keep performance review data</small>
                </div>
                
                <div class="setting-item">
                    <label class="setting-label">Anonymous Analytics</label>
                    <label class="toggle-switch">
                        <input type="checkbox" name="anonymous_analytics" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <small class="setting-help">Allow anonymous usage analytics to improve the system</small>
                </div>
                
                <div class="setting-item">
                    <label class="setting-label">Employee Data Visibility</label>
                    <select name="employee_visibility" class="form-control">
                        <option value="own">Own data only</option>
                        <option value="team" selected>Team data (managers)</option>
                        <option value="department">Department data</option>
                        <option value="company">Company-wide (admins)</option>
                    </select>
                    <small class="setting-help">What performance data employees can view</small>
                </div>
                
                <div class="setting-item">
                    <label class="setting-label">Export Permissions</label>
                    <select name="export_permissions" class="form-control">
                        <option value="admin">Admins only</option>
                        <option value="manager" selected>Managers and above</option>
                        <option value="all">All users</option>
                    </select>
                    <small class="setting-help">Who can export performance data</small>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('styles')
<style>
.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--spacing-xl);
}

.setting-item {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.setting-label {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.875rem;
}

.setting-help {
    font-size: 0.75rem;
    color: var(--text-muted);
    line-height: 1.4;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 3.5rem;
    height: 2rem;
    align-self: flex-start;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.3s;
    border-radius: 2rem;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 1.5rem;
    width: 1.5rem;
    left: 0.25rem;
    bottom: 0.25rem;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

input:checked + .toggle-slider {
    background-color: var(--success-color);
}

input:checked + .toggle-slider:before {
    transform: translateX(1.5rem);
}

.card {
    margin-bottom: var(--spacing-xl);
}

.card-subtitle {
    color: var(--text-muted);
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

@media (max-width: 768px) {
    .settings-grid {
        grid-template-columns: 1fr;
        gap: var(--spacing-lg);
    }
    
    .page-actions {
        flex-direction: column;
        gap: var(--spacing-sm);
    }
}
</style>
@endpush

@push('scripts')
<script>
function saveSettings() {
    const form = document.getElementById('settingsForm');
    const formData = new FormData(form);
    
    if (window.PMS && window.PMS.notifications) {
        window.PMS.notifications.info('Saving settings...');
    }
    
    // In a real implementation, submit the form data to the server
    setTimeout(() => {
        if (window.PMS && window.PMS.notifications) {
            window.PMS.notifications.success('Settings saved successfully!');
        }
    }, 1000);
}

function resetSettings() {
    if (confirm('Are you sure you want to reset all settings to their default values? This action cannot be undone.')) {
        if (window.PMS && window.PMS.notifications) {
            window.PMS.notifications.info('Resetting settings to defaults...');
        }
        
        // Reset form to defaults
        const form = document.getElementById('settingsForm');
        form.reset();
        
        // Set default values
        form.querySelector('[name="review_frequency"]').value = 'quarterly';
        form.querySelector('[name="scale_type"]').value = '1-5';
        form.querySelector('[name="reminder_frequency"]').value = 'weekly';
        form.querySelector('[name="data_retention"]').value = '3years';
        form.querySelector('[name="employee_visibility"]').value = 'team';
        form.querySelector('[name="export_permissions"]').value = 'manager';
        
        // Check default checkboxes
        form.querySelector('[name="auto_notifications"]').checked = true;
        form.querySelector('[name="require_manager_approval"]').checked = true;
        form.querySelector('[name="allow_self_review"]').checked = true;
        form.querySelector('[name="weighted_scoring"]').checked = true;
        form.querySelector('[name="auto_calculate"]').checked = true;
        form.querySelector('[name="email_reminders"]').checked = true;
        form.querySelector('[name="deadline_alerts"]').checked = true;
        form.querySelector('[name="performance_updates"]').checked = true;
        form.querySelector('[name="anonymous_analytics"]').checked = true;
        
        setTimeout(() => {
            if (window.PMS && window.PMS.notifications) {
                window.PMS.notifications.success('Settings reset to defaults!');
            }
        }, 1000);
    }
}

// Form change detection
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('settingsForm');
    let hasChanges = false;
    
    form.addEventListener('change', function() {
        hasChanges = true;
    });
    
    // Warn user about unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (hasChanges) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        }
    });
});
</script>
@endpush
