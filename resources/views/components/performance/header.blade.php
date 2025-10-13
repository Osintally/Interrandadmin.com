<!-- Performance Management System Header -->
<header class="performance-header">
    <div class="header-left">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="breadcrumb">
            @yield('breadcrumbs')
        </div>
    </div>
    
    <div class="header-center">
        <h1 class="page-title">@yield('page-title', 'Performance Management')</h1>
        @hasSection('page-subtitle')
            <p class="page-subtitle">@yield('page-subtitle')</p>
        @endif
    </div>
    
    <div class="header-right">
        <div class="header-actions">
            @yield('page-actions')
        </div>
        
        <div class="user-menu">
            <div class="user-info">
                <span class="user-name">{{ auth()->user()->first_name ?? 'User' }}</span>
                <span class="user-role">Performance Manager</span>
            </div>
            
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
        </div>
    </div>
</header>
