@extends('layouts.pms-app')

@section('title', 'User Management')

@section('page-title', 'User Management')
@section('page-subtitle', 'Manage users and their performance management access')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">User Management</h1>
        <p class="page-subtitle">Configure user access and permissions for the performance management system</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-outline" onclick="exportUsers()">
            <i class="fas fa-download"></i>
            Export Users
        </button>
        <button class="btn btn-primary" onclick="refreshUsers()">
            <i class="fas fa-sync-alt"></i>
            Refresh
        </button>
    </div>
</div>

<!-- Users Summary -->
<div class="dashboard-grid stats">
    @php 
        $totalUsers = $users->total();
        $activeUsers = $users->where('is_active', 1)->count();
    @endphp
    
    <div class="stat-card">
        <span class="stat-number">{{ $totalUsers }}</span>
        <span class="stat-label">Total Users</span>
        <div class="stat-change positive">
            <i class="fas fa-users"></i>
            All users
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">{{ $activeUsers }}</span>
        <span class="stat-label">Active Users</span>
        <div class="stat-change positive">
            <i class="fas fa-user-check"></i>
            Currently active
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">{{ $users->where('is_active', 0)->count() }}</span>
        <span class="stat-label">Inactive Users</span>
        <div class="stat-change negative">
            <i class="fas fa-user-times"></i>
            Deactivated
        </div>
    </div>
    
    <div class="stat-card">
        <span class="stat-number">{{ number_format($totalUsers > 0 ? ($activeUsers / $totalUsers) * 100 : 0, 1) }}%</span>
        <span class="stat-label">Activity Rate</span>
        <div class="stat-change positive">
            <i class="fas fa-percentage"></i>
            Active ratio
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Users List</h3>
        <div class="card-actions">
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Search users..." id="userSearch">
            </div>
        </div>
    </div>
    
    <div class="card-body">
        @if($users->count() > 0)
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Active</th>
                        <th>Performance Access</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">
                                    {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                </div>
                                <div class="user-details">
                                    <div class="user-name">{{ $user->first_name }} {{ $user->last_name }}</div>
                                    <div class="user-id">#{{ $user->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->roles->count() > 0)
                                @foreach($user->roles as $role)
                                <span class="badge badge-primary">{{ $role->name }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">No role assigned</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge {{ $user->is_active ? 'active' : 'inactive' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @if($user->last_login_at)
                                {{ \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() }}
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                        <td>
                            <div class="permission-toggles">
                                <label class="toggle-switch">
                                    <input type="checkbox" {{ $user->can('view_performance') ? 'checked' : '' }} 
                                           onchange="togglePermission({{ $user->id }}, 'view_performance', this.checked)">
                                    <span class="toggle-slider"></span>
                                </label>
                                <small>View Access</small>
                            </div>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-outline btn-sm" onclick="viewUser({{ $user->id }})" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-primary btn-sm" onclick="editUser({{ $user->id }})" title="Edit User">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($user->is_active)
                                <button class="btn btn-warning btn-sm" onclick="deactivateUser({{ $user->id }})" title="Deactivate">
                                    <i class="fas fa-pause"></i>
                                </button>
                                @else
                                <button class="btn btn-success btn-sm" onclick="activateUser({{ $user->id }})" title="Activate">
                                    <i class="fas fa-play"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="pagination-wrapper">
            {{ $users->links() }}
        </div>
        @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="empty-state-title">No Users Found</div>
            <div class="empty-state-description">
                No users are currently available in the system.
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.user-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
}

.user-avatar {
    width: 2.5rem;
    height: 2.5rem;
    background: var(--primary-color);
    color: var(--white);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
}

.user-details {
    flex: 1;
}

.user-name {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.125rem;
}

.user-id {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-primary {
    background-color: var(--primary-color);
    color: var(--white);
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-badge.active {
    background-color: var(--success-color);
    color: var(--white);
}

.status-badge.inactive {
    background-color: var(--danger-color);
    color: var(--white);
}

.permission-toggles {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 3rem;
    height: 1.5rem;
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
    border-radius: 1.5rem;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 1.125rem;
    width: 1.125rem;
    left: 0.1875rem;
    bottom: 0.1875rem;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background-color: var(--success-color);
}

input:checked + .toggle-slider:before {
    transform: translateX(1.5rem);
}

.action-buttons {
    display: flex;
    gap: 0.25rem;
}

.search-container {
    position: relative;
    width: 250px;
}

.search-input {
    width: 100%;
    padding: 0.375rem 0.75rem 0.375rem 2rem;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    font-size: 0.875rem;
}

.search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 0.875rem;
}

.pagination-wrapper {
    margin-top: var(--spacing-lg);
    display: flex;
    justify-content: center;
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
}

@media (max-width: 768px) {
    .card-header {
        flex-direction: column;
        gap: var(--spacing-md);
    }
    
    .search-container {
        width: 100%;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .user-info {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-sm);
    }
}
</style>
@endpush

@push('scripts')
<script>
// Search functionality
document.getElementById('userSearch')?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

function togglePermission(userId, permission, enabled) {
    if (window.PMS && window.PMS.notifications) {
        window.PMS.notifications.info(`${enabled ? 'Granting' : 'Revoking'} ${permission} for user ${userId}...`);
    }
    
    // In a real implementation, make API call to update permission
    setTimeout(() => {
        if (window.PMS && window.PMS.notifications) {
            window.PMS.notifications.success(`Permission ${enabled ? 'granted' : 'revoked'} successfully!`);
        }
    }, 1000);
}

function viewUser(userId) {
    if (window.PMS && window.PMS.notifications) {
        window.PMS.notifications.info('Opening user details...');
    }
    // In a real implementation, open user details modal or navigate to user page
}

function editUser(userId) {
    if (window.PMS && window.PMS.notifications) {
        window.PMS.notifications.info('Opening user editor...');
    }
    // In a real implementation, open user edit modal or navigate to edit page
}

function activateUser(userId) {
    if (confirm('Are you sure you want to activate this user?')) {
        if (window.PMS && window.PMS.notifications) {
            window.PMS.notifications.success('User activated successfully!');
        }
        setTimeout(() => window.location.reload(), 1000);
    }
}

function deactivateUser(userId) {
    if (confirm('Are you sure you want to deactivate this user?')) {
        if (window.PMS && window.PMS.notifications) {
            window.PMS.notifications.success('User deactivated successfully!');
        }
        setTimeout(() => window.location.reload(), 1000);
    }
}

function refreshUsers() {
    if (window.PMS && window.PMS.notifications) {
        window.PMS.notifications.info('Refreshing user list...');
    }
    setTimeout(() => window.location.reload(), 500);
}

function exportUsers() {
    if (window.PMS && window.PMS.notifications) {
        window.PMS.notifications.success('Users list exported successfully!');
    }
    // In a real implementation, trigger download of users export
}
</script>
@endpush
