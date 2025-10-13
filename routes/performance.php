<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Performance\DashboardController;
use App\Http\Controllers\Performance\TemplateController;
use App\Http\Controllers\Performance\ReportController;
use App\Http\Controllers\Performance\KPIController;
use App\Http\Controllers\Performance\GoalsController;
use App\Http\Controllers\Performance\AlertsController;

/*
|--------------------------------------------------------------------------
| Performance Management Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the enhanced Performance Management system.
| All routes are protected by authentication and role-based permissions.
|
*/

Route::middleware(['auth', 'web'])->group(function () {
    
    // Performance Dashboard Routes
    Route::prefix('performance')->name('performance.')->group(function () {
        
        // Main dashboard route (handled in web.php)
        
        // Leaderboards Routes  
        Route::get('/leaderboards', [App\Http\Controllers\Performance\LeaderboardController::class, 'index'])
            ->name('leaderboards.index');
        
        // Main performance index
        Route::get('/index', [App\Http\Controllers\Performance\PerformanceController::class, 'index'])
            ->name('index');
            
        // Analytics Routes
        Route::get('/analytics', [App\Http\Controllers\Performance\AnalyticsController::class, 'index'])
            ->name('analytics.index');
            
        // User Management Routes (for admin)
        Route::middleware(['permission:manage_users'])->group(function () {
            Route::get('/users', [App\Http\Controllers\Performance\UserController::class, 'index'])
                ->name('users.index');
        });
        
        // Settings Routes (for admin)
        Route::middleware(['permission:manage_settings'])->group(function () {
            Route::get('/settings', [App\Http\Controllers\Performance\SettingsController::class, 'index'])
                ->name('settings.index');
        });
    });

    // Performance Templates Routes
    Route::prefix('performance/templates')->name('performance.templates.')->group(function () {
        Route::get('/', [TemplateController::class, 'index'])->name('index');
        Route::get('/create', [TemplateController::class, 'create'])->name('create');
        Route::get('/builder/{id?}', [TemplateController::class, 'builder'])->name('builder');
        Route::post('/', [TemplateController::class, 'store'])->name('store');
        Route::get('/{template}', [TemplateController::class, 'show'])->name('show');
        Route::get('/{template}/edit', [TemplateController::class, 'edit'])->name('edit');
        Route::put('/{template}', [TemplateController::class, 'update'])->name('update');
        Route::delete('/{template}', [TemplateController::class, 'destroy'])->name('destroy');
        Route::post('/{template}/duplicate', [TemplateController::class, 'duplicate'])->name('duplicate');
        Route::post('/{template}/publish', [TemplateController::class, 'publish'])->name('publish');
        Route::post('/{template}/unpublish', [TemplateController::class, 'unpublish'])->name('unpublish');
        Route::post('/{template}/kpi-preview', [TemplateController::class, 'getKPIPreview'])->name('kpi-preview');
        Route::get('/{template}/statistics', [TemplateController::class, 'getStatistics'])->name('statistics');
        Route::get('/kpis/available', [TemplateController::class, 'getKPIs'])->name('kpis.available');
        Route::get('/field-types', [TemplateController::class, 'getFieldTypes'])->name('field-types');
    });

    // Performance Reports Routes
    Route::prefix('performance/reports')->name('performance.reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/create', [ReportController::class, 'create'])->name('create');
        Route::post('/', [ReportController::class, 'store'])->name('store');
        Route::get('/{report}', [ReportController::class, 'show'])->name('show');
        Route::get('/{report}/edit', [ReportController::class, 'edit'])->name('edit');
        Route::put('/{report}', [ReportController::class, 'update'])->name('update');
        Route::delete('/{report}', [ReportController::class, 'destroy'])->name('destroy');
        Route::post('/{report}/save-draft', [ReportController::class, 'saveDraft'])->name('save-draft');
        Route::post('/{report}/approve', [ReportController::class, 'approve'])->name('approve');
        Route::post('/{report}/reject', [ReportController::class, 'reject'])->name('reject');
        Route::post('/kpi-preview', [ReportController::class, 'getKPIPreview'])->name('kpi-preview');
        Route::post('/export', [ReportController::class, 'export'])->name('export');
        Route::get('/statistics', [ReportController::class, 'getStats'])->name('statistics');
    });

    // KPI Management Routes
    Route::prefix('performance/kpis')->name('performance.kpis.')->group(function () {
        Route::get('/', [KPIController::class, 'index'])->name('index');
        Route::get('/create', [KPIController::class, 'create'])->name('create');
        Route::post('/', [KPIController::class, 'store'])->name('store');
        Route::get('/{kpi}', [KPIController::class, 'show'])->name('show');
        Route::get('/{kpi}/edit', [KPIController::class, 'edit'])->name('edit');
        Route::put('/{kpi}', [KPIController::class, 'update'])->name('update');
        Route::delete('/{kpi}', [KPIController::class, 'destroy'])->name('destroy');
        Route::get('/{kpi}/analytics', [KPIController::class, 'analytics'])->name('analytics');
        Route::post('/{kpi}/goals', [KPIController::class, 'setGoals'])->name('goals');
        Route::get('/{kpi}/performance-data', [KPIController::class, 'getPerformanceData'])->name('performance-data');
        Route::get('/export', [KPIController::class, 'export'])->name('export');
    });

    // Performance Goals Routes
    Route::prefix('performance/goals')->name('performance.goals.')->group(function () {
        Route::get('/', [GoalsController::class, 'index'])->name('index');
                Route::get('/create', [GoalsController::class, 'create'])->name('create');
        Route::post('/', [GoalsController::class, 'store'])->name('store');
                Route::get('/{goal}', [GoalsController::class, 'show'])->name('show');
        Route::put('/{goal}/update-score', [GoalsController::class, 'updateScore'])->name('update-score');
        Route::delete('/{goal}', [GoalsController::class, 'destroy'])->name('destroy');
        Route::get('/export', [GoalsController::class, 'export'])->name('export');
        Route::get('/data', [GoalsController::class, 'getGoalsData'])->name('data');
    });

    // Performance Alerts Routes
    Route::prefix('performance/alerts')->name('performance.alerts.')->group(function () {
        Route::get('/', [AlertsController::class, 'index'])->name('index');
        Route::get('/{alert}', [AlertsController::class, 'show'])->name('show');
        Route::post('/{alert}/mark-read', [AlertsController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [AlertsController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/{alert}/resolve', [AlertsController::class, 'resolve'])->name('resolve');
        Route::delete('/{alert}', [AlertsController::class, 'destroy'])->name('destroy');
        Route::get('/export', [AlertsController::class, 'export'])->name('export');
        Route::get('/data', [AlertsController::class, 'getAlertsData'])->name('data');
    });

    // API Routes for AJAX requests
    Route::prefix('api/performance')->name('api.performance.')->group(function () {
        
        // Dashboard API
        Route::get('/dashboard/summary', [DashboardController::class, 'getSummary'])->name('dashboard.summary');
        Route::get('/dashboard/trends', [DashboardController::class, 'getTrends'])->name('dashboard.trends');
        Route::get('/dashboard/kpi-breakdown', [DashboardController::class, 'getKPIBreakdown'])->name('dashboard.kpi-breakdown');
        Route::get('/dashboard/top-performers', [DashboardController::class, 'getTopPerformersAPI'])->name('dashboard.top-performers');
        Route::get('/dashboard/alerts', [DashboardController::class, 'getAlerts'])->name('dashboard.alerts');
        Route::post('/dashboard/alerts/{alert}/read', [DashboardController::class, 'markAlertAsRead'])->name('dashboard.alerts.read');
        
        // Templates API
        Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
        Route::post('/templates', [TemplateController::class, 'store'])->name('templates.store');
        Route::get('/templates/{template}', [TemplateController::class, 'show'])->name('templates.show');
        Route::put('/templates/{template}', [TemplateController::class, 'update'])->name('templates.update');
        Route::delete('/templates/{template}', [TemplateController::class, 'destroy'])->name('templates.destroy');
        Route::post('/templates/{template}/kpi-preview', [TemplateController::class, 'getKPIPreview'])->name('templates.kpi-preview');
        Route::get('/templates/{template}/statistics', [TemplateController::class, 'getStatistics'])->name('templates.statistics');
        Route::get('/templates/kpis/available', [TemplateController::class, 'getKPIs'])->name('templates.kpis.available');
        Route::get('/templates/field-types', [TemplateController::class, 'getFieldTypes'])->name('templates.field-types');
        
        // Reports API
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
        Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
        Route::put('/reports/{report}', [ReportController::class, 'update'])->name('reports.update');
        Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');
        Route::post('/reports/{report}/save-draft', [ReportController::class, 'saveDraft'])->name('reports.save-draft');
        Route::post('/reports/{report}/approve', [ReportController::class, 'approve'])->name('reports.approve');
        Route::post('/reports/{report}/reject', [ReportController::class, 'reject'])->name('reports.reject');
        Route::post('/reports/kpi-preview', [ReportController::class, 'getKPIPreview'])->name('reports.kpi-preview');
        Route::get('/reports/statistics', [ReportController::class, 'getStats'])->name('reports.statistics');
        
        // KPIs API
        Route::get('/kpis', [KPIController::class, 'index'])->name('kpis.index');
        Route::post('/kpis', [KPIController::class, 'store'])->name('kpis.store');
        Route::get('/kpis/{kpi}', [KPIController::class, 'show'])->name('kpis.show');
        Route::put('/kpis/{kpi}', [KPIController::class, 'update'])->name('kpis.update');
        Route::delete('/kpis/{kpi}', [KPIController::class, 'destroy'])->name('kpis.destroy');
        Route::get('/kpis/{kpi}/analytics', [KPIController::class, 'analytics'])->name('kpis.analytics');
        Route::post('/kpis/{kpi}/goals', [KPIController::class, 'setGoals'])->name('kpis.goals');
        Route::get('/kpis/{kpi}/performance-data', [KPIController::class, 'getPerformanceData'])->name('kpis.performance-data');
    });
});
