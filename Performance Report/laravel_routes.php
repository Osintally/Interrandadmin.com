<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DepartmentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Dashboard Routes
Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/stats', [DashboardController::class, 'getStats'])->name('stats');
    Route::post('/refresh', [DashboardController::class, 'refreshData'])->name('refresh');
});

// Employee Routes
Route::prefix('employees')->name('employees.')->group(function () {
    Route::get('/', [EmployeeController::class, 'index'])->name('index');
    Route::get('/create', [EmployeeController::class, 'create'])->name('create');
    Route::post('/', [EmployeeController::class, 'store'])->name('store');
    Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
    Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
    Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
    Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
});

// Performance Routes
Route::prefix('performance')->name('performance.')->group(function () {
    Route::get('/', [PerformanceController::class, 'index'])->name('index');
    Route::get('/create', [PerformanceController::class, 'create'])->name('create');
    Route::post('/', [PerformanceController::class, 'store'])->name('store');
    Route::get('/{performance}', [PerformanceController::class, 'show'])->name('show');
    Route::get('/{performance}/edit', [PerformanceController::class, 'edit'])->name('edit');
    Route::put('/{performance}', [PerformanceController::class, 'update'])->name('update');
    Route::delete('/{performance}', [PerformanceController::class, 'destroy'])->name('destroy');
    Route::get('/employee/{employee}', [PerformanceController::class, 'byEmployee'])->name('by-employee');
    Route::get('/department/{department}', [PerformanceController::class, 'byDepartment'])->name('by-department');
});

// Report Routes
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/export', [ReportController::class, 'export'])->name('export');
    Route::post('/generate', [ReportController::class, 'generate'])->name('generate');
});

// Template Routes
Route::prefix('templates')->name('templates.')->group(function () {
    Route::get('/', [TemplateController::class, 'index'])->name('index');
    Route::get('/create', [TemplateController::class, 'create'])->name('create');
    Route::post('/', [TemplateController::class, 'store'])->name('store');
    Route::get('/{template}', [TemplateController::class, 'show'])->name('show');
    Route::get('/{template}/edit', [TemplateController::class, 'edit'])->name('edit');
    Route::put('/{template}', [TemplateController::class, 'update'])->name('update');
    Route::delete('/{template}', [TemplateController::class, 'destroy'])->name('destroy');
    Route::post('/{template}/clone', [TemplateController::class, 'clone'])->name('clone');
});

// Leaderboard Routes
Route::prefix('leaderboard')->name('leaderboard.')->group(function () {
    Route::get('/', [LeaderboardController::class, 'index'])->name('index');
    Route::get('/department/{department?}', [LeaderboardController::class, 'byDepartment'])->name('department');
    Route::get('/period/{period}', [LeaderboardController::class, 'byPeriod'])->name('period');
});

// Analytics Routes
Route::prefix('analytics')->name('analytics.')->group(function () {
    Route::get('/', [AnalyticsController::class, 'index'])->name('index');
    Route::get('/performance-trends', [AnalyticsController::class, 'performanceTrends'])->name('performance-trends');
    Route::get('/department-analytics', [AnalyticsController::class, 'departmentAnalytics'])->name('department-analytics');
    Route::get('/distribution', [AnalyticsController::class, 'performanceDistribution'])->name('distribution');
    Route::post('/recommendations', [AnalyticsController::class, 'generateRecommendations'])->name('recommendations');
});

// Department Routes
Route::prefix('departments')->name('departments.')->group(function () {
    Route::get('/', [DepartmentController::class, 'index'])->name('index');
    Route::post('/', [DepartmentController::class, 'store'])->name('store');
    Route::get('/{department}', [DepartmentController::class, 'show'])->name('show');
    Route::put('/{department}', [DepartmentController::class, 'update'])->name('update');
    Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('destroy');
});

// API Routes for AJAX calls
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/performance/trends', [AnalyticsController::class, 'performanceTrends'])->name('performance.trends');
    Route::get('/leaderboard/{period?}', [LeaderboardController::class, 'getLeaderboardData'])->name('leaderboard.data');
    Route::get('/analytics/distribution', [AnalyticsController::class, 'getDistributionData'])->name('analytics.distribution');
});