<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Performance Management') - {{ config('app.name') }}</title>
    
    <!-- Keep existing app fonts/styles that don't conflict -->
    @if(file_exists(public_path('css/app.css')))
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @endif
    
    <!-- PMS Specific Styles -->
    <link href="{{ asset('css/pms-styles.css') }}" rel="stylesheet">
    
    <!-- External CDN Dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    
    @stack('styles')
    
    <!-- Page Actions Styling -->
    <style>
    .page-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: var(--spacing-sm, 0.75rem);
        margin-bottom: var(--spacing-lg, 1.5rem);
        padding: var(--spacing-md, 1rem) 0;
        border-bottom: 1px solid var(--border-color, #e5e7eb);
    }
    
    .page-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        cursor: pointer;
    }
    
    .page-actions .btn-primary {
        background-color: var(--primary-color, #4f46e5);
        color: white;
        border-color: var(--primary-color, #4f46e5);
    }
    
    .page-actions .btn-primary:hover {
        background-color: var(--primary-dark, #3730a3);
        border-color: var(--primary-dark, #3730a3);
    }
    
    .page-actions .btn-outline {
        background-color: transparent;
        color: var(--text-primary, #374151);
        border-color: var(--border-color, #d1d5db);
    }
    
    .page-actions .btn-outline:hover {
        background-color: var(--bg-secondary, #f9fafb);
        border-color: var(--text-primary, #374151);
    }
    
    .page-actions .btn-success {
        background-color: var(--success-color, #10b981);
        color: white;
        border-color: var(--success-color, #10b981);
    }
    
    .page-actions .btn-success:hover {
        background-color: var(--success-dark, #059669);
        border-color: var(--success-dark, #059669);
    }
    
    @media (max-width: 768px) {
        .page-actions {
            flex-direction: column;
            align-items: stretch;
            gap: var(--spacing-sm, 0.5rem);
        }
        
        .page-actions .btn {
            justify-content: center;
        }
    }
    
    /* Performance Sidebar Styles */
    .performance-sidebar {
        width: 250px;
        background: var(--white);
        border-right: 1px solid var(--border-color);
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }
    
    .sidebar-header {
        padding: var(--spacing-lg);
        border-bottom: 1px solid var(--border-color);
        background: var(--primary-color);
        color: var(--white);
    }
    
    .sidebar-header .logo {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-size: 1.25rem;
        font-weight: 600;
    }
    
    .sidebar-nav {
        flex: 1;
        padding: var(--spacing-md) 0;
        overflow-y: auto;
    }
    
    .nav-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    
    .nav-item {
        margin: 0;
    }
    
    .nav-link {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        padding: var(--spacing-md) var(--spacing-lg);
        color: var(--text-primary);
        text-decoration: none;
        transition: var(--transition);
        border-left: 3px solid transparent;
    }
    
    .nav-link:hover {
        background: var(--bg-secondary);
        color: var(--primary-color);
    }
    
    .nav-link.active {
        background: rgba(79, 70, 229, 0.1);
        color: var(--primary-color);
        border-left-color: var(--primary-color);
    }
    
    .nav-link i {
        width: 20px;
        text-align: center;
    }
    
    .sidebar-footer {
        padding: var(--spacing-md);
        border-top: 1px solid var(--border-color);
    }
    
    /* Performance Header Styles */
    .performance-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--spacing-md) var(--spacing-lg);
        background: var(--white);
        border-bottom: 1px solid var(--border-color);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 0;
        z-index: 999;
    }
    
    .header-left {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
    }
    
    .sidebar-toggle {
        background: none;
        border: none;
        font-size: 1.25rem;
        color: var(--text-primary);
        cursor: pointer;
        padding: var(--spacing-sm);
        border-radius: var(--border-radius);
        transition: var(--transition);
    }
    
    .sidebar-toggle:hover {
        background: var(--bg-secondary);
    }
    
    .breadcrumb {
        display: flex;
        align-items: center;
        gap: var(--spacing-xs);
        color: var(--text-secondary);
        font-size: 0.875rem;
    }
    
    .header-center {
        flex: 1;
        text-align: center;
    }
    
    .page-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }
    
    .page-subtitle {
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin: 0.25rem 0 0 0;
    }
    
    .header-right {
        display: flex;
        align-items: center;
        gap: var(--spacing-lg);
    }
    
    .header-actions {
        display: flex;
        gap: var(--spacing-sm);
    }
    
    .user-menu {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        padding: var(--spacing-sm);
        border-radius: var(--border-radius);
        transition: var(--transition);
    }
    
    .user-menu:hover {
        background: var(--bg-secondary);
    }
    
    .user-info {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }
    
    .user-name {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.875rem;
    }
    
    .user-role {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    
    .user-avatar {
        font-size: 1.5rem;
        color: var(--primary-color);
    }
    
    /* Main content adjustment */
    .main-content {
        margin-left: 250px;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }
    
    .page-content {
        flex: 1;
        padding: var(--spacing-lg);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .performance-sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }
        
        .performance-sidebar.open {
            transform: translateX(0);
        }
        
        .main-content {
            margin-left: 0;
        }
        
        .header-center {
            display: none;
        }
    }
    </style>
</head>
<body>
    <div class="app-container">
        @include('components.performance.sidebar')
        
        <main class="main-content" id="mainContent">
            @include('components.performance.header')
            
            <div class="page-content">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="toast-container"></div>

    <!-- PMS Scripts -->
    <script src="{{ asset('js/pms-core.js') }}"></script>
    <script src="{{ asset('js/pms-api.js') }}"></script>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.performance-sidebar');
            sidebar.classList.toggle('open');
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.performance-sidebar');
            const toggle = document.querySelector('.sidebar-toggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !toggle.contains(e.target) && 
                sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
            }
        });
    </script>

    <!-- Custom Scripts -->
    @stack('scripts')

    <!-- Initialize PMS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.PMS.init({
                user: @json(auth()->user()),
                permissions: @json(auth()->user()->getAllPermissions()->pluck('name')),
                csrf_token: '{{ csrf_token() }}',
                api_url: '{{ url("/api") }}'
            });

            // Mobile menu toggle
            const menuToggle = document.querySelector('.mobile-menu-toggle, .small-view-button');
            if (menuToggle) {
                menuToggle.addEventListener('click', () => {
                    if (window.PMS && window.PMS.sidebar) {
                        window.PMS.sidebar.toggle();
                    }
                });
            }
        });
    </script>
</body>
</html>