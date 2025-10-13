<!-- Performance Management System Sidebar -->
<aside class="performance-sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-chart-line"></i>
            <span>Performance</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item">
                <a href="{{ route('performance.dashboard.index') }}" class="nav-link {{ request()->routeIs('performance.dashboard.*') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('performance.templates.index') }}" class="nav-link {{ request()->routeIs('performance.templates.*') ? 'active' : '' }}">
                    <i class="fas fa-file-alt"></i>
                    <span>Templates</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('performance.templates.builder') }}" class="nav-link {{ request()->routeIs('performance.templates.builder') ? 'active' : '' }}">
                    <i class="fas fa-tools"></i>
                    <span>Template Builder</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('performance.goals.index') }}" class="nav-link {{ request()->routeIs('performance.goals.*') ? 'active' : '' }}">
                    <i class="fas fa-bullseye"></i>
                    <span>Goals & KPI</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('performance.reports.index') }}" class="nav-link {{ request()->routeIs('performance.reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('performance.analytics.index') }}" class="nav-link {{ request()->routeIs('performance.analytics.*') ? 'active' : '' }}">
                    <i class="fas fa-analytics"></i>
                    <span>Analytics</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('performance.leaderboards.index') }}" class="nav-link {{ request()->routeIs('performance.leaderboards.*') ? 'active' : '' }}">
                    <i class="fas fa-trophy"></i>
                    <span>Leaderboards</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('performance.users.index') }}" class="nav-link {{ request()->routeIs('performance.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('performance.settings.index') }}" class="nav-link {{ request()->routeIs('performance.settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <a href="{{ route('home') }}" class="nav-link">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Main</span>
        </a>
    </div>
</aside>
