<?php

use Modules\Essentials\Http\Controllers;
use Modules\Essentials\Http\Controllers\MemoController;
use Illuminate\Support\Facades\Route;


Route::middleware(['web', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu'])->group(function () {
    Route::prefix('essentials')->group(function () {
        Route::get('/dashboard', [Modules\Essentials\Http\Controllers\DashboardController::class, 'essentialsDashboard']);
        Route::get('/install', [Modules\Essentials\Http\Controllers\InstallController::class, 'index']);
        Route::post('/install', [Modules\Essentials\Http\Controllers\InstallController::class, 'install']);
        Route::get('/install/update', [Modules\Essentials\Http\Controllers\InstallController::class, 'update']);
        Route::get('/install/uninstall', [Modules\Essentials\Http\Controllers\InstallController::class, 'uninstall']);

        Route::get('/', [Modules\Essentials\Http\Controllers\EssentialsController::class, 'index']);

        // Memo routes
        Route::resource('memos', Modules\Essentials\Http\Controllers\MemoController::class)->except(['create', 'edit']);
        Route::post('memos/{memo}/send', [Modules\Essentials\Http\Controllers\MemoController::class, 'send'])->name('memos.send');
        Route::get('memos/{memo}/attachments/{attachment_id}', [Modules\Essentials\Http\Controllers\MemoController::class, 'downloadAttachment'])->name('memos.download_attachment');
        Route::post('memos/{memo}/mark-read', [Modules\Essentials\Http\Controllers\MemoController::class, 'markAsRead'])->name('memos.mark_read');
        Route::get('users/search', gi [Modules\Essentials\Http\Controllers\MemoController::class, 'searchUsers'])->name('users.search');

        // Document routes
        Route::resource('document', Modules\Essentials\Http\Controllers\DocumentController::class)->only(['index', 'store', 'destroy', 'show']);
        Route::get('document/{document}/download', [Modules\Essentials\Http\Controllers\DocumentController::class, 'download'])->name('document.download');

        // Document share routes
        Route::resource('document-share', Modules\Essentials\Http\Controllers\DocumentShareController::class)->only(['edit', 'update']);

        // Todo routes
        Route::resource('todo', Modules\Essentials\Http\Controllers\ToDoController::class);
        Route::post('todo/add-comment', [Modules\Essentials\Http\Controllers\ToDoController::class, 'addComment'])->name('todo.add_comment');
        Route::delete('todo/delete-comment/{comment}', [Modules\Essentials\Http\Controllers\ToDoController::class, 'deleteComment'])->name('todo.delete_comment');
        Route::delete('todo/delete-document/{document}', [Modules\Essentials\Http\Controllers\ToDoController::class, 'deleteDocument'])->name('todo.delete_document');
        Route::post('todo/upload-document', [Modules\Essentials\Http\Controllers\ToDoController::class, 'uploadDocument'])->name('todo.upload_document');
        Route::get('todo/{todo}/shared-docs', [Modules\Essentials\Http\Controllers\ToDoController::class, 'viewSharedDocs'])->name('todo.view_shared_docs');

        // Reminder routes
        Route::resource('reminder', Modules\Essentials\Http\Controllers\ReminderController::class)->only(['index', 'store', 'edit', 'update', 'destroy', 'show']);

        // Message routes
        Route::get('get-new-messages', [Modules\Essentials\Http\Controllers\EssentialsMessageController::class, 'getNewMessages'])->name('messages.get_new');
        Route::resource('messages', Modules\Essentials\Http\Controllers\EssentialsMessageController::class)->only(['index', 'store', 'destroy']);

        // Allowance and deduction routes
        Route::resource('allowance-deduction', Modules\Essentials\Http\Controllers\EssentialsAllowanceAndDeductionController::class);

        // Knowledge base routes
        Route::resource('knowledge-base', Modules\Essentials\Http\Controllers\KnowledgeBaseController::class);

        // Sales targets
        Route::get('user-sales-targets', [Modules\Essentials\Http\Controllers\DashboardController::class, 'getUserSalesTargets'])->name('user_sales_targets');
    });

    Route::prefix('hrm')->group(function () {
        Route::get('/dashboard', [Modules\Essentials\Http\Controllers\DashboardController::class, 'hrmDashboard'])->name('hrm.dashboard');
        
        // Leave management
        Route::resource('/leave-type', Modules\Essentials\Http\Controllers\EssentialsLeaveTypeController::class);
        Route::resource('/leave', Modules\Essentials\Http\Controllers\EssentialsLeaveController::class);
        Route::post('/change-status', [Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'changeStatus'])->name('leave.change_status');
        Route::get('/leave/activity/{leave}', [Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'activity'])->name('leave.activity');
        Route::get('/user-leave-summary', [Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'getUserLeaveSummary'])->name('leave.user_summary');
        Route::get('/change-leave-status', [Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'changeLeaveStatus'])->name('leave.change_status_form');

        // Settings
        Route::get('/settings', [Modules\Essentials\Http\Controllers\EssentialsSettingsController::class, 'edit'])->name('hrm.settings.edit');
        Route::post('/settings', [Modules\Essentials\Http\Controllers\EssentialsSettingsController::class, 'update'])->name('hrm.settings.update');

        // Basic Attendance
        Route::post('/import-attendance', [Modules\Essentials\Http\Controllers\AttendanceController::class, 'importAttendance'])->name('attendance.import');
        Route::resource('/attendance', Modules\Essentials\Http\Controllers\AttendanceController::class);
        Route::post('/clock-in-clock-out', [Modules\Essentials\Http\Controllers\AttendanceController::class, 'clockInClockOut'])->name('attendance.clock');
        Route::post('/validate-clock', [Modules\Essentials\Http\Controllers\AttendanceController::class, 'validateClockInClockOut'])->name('attendance.validate_clock');
        Route::get('/get-attendance-by-shift', [Modules\Essentials\Http\Controllers\AttendanceController::class, 'getAttendanceByShift'])->name('attendance.by_shift');
        Route::get('/get-attendance-by-date', [Modules\Essentials\Http\Controllers\AttendanceController::class, 'getAttendanceByDate'])->name('attendance.by_date');
        Route::get('/get-attendance-row/{user}', [Modules\Essentials\Http\Controllers\AttendanceController::class, 'getAttendanceRow'])->name('attendance.user_row');
        Route::get('/user-attendance-summary', [Modules\Essentials\Http\Controllers\AttendanceController::class, 'getUserAttendanceSummary'])->name('attendance.user_summary');

        // Enhanced Attendance Routes
        Route::prefix('enhanced-attendance')->name('enhanced.attendance.')->group(function () {
            Route::get('/', [Modules\Essentials\Http\Controllers\EnhancedAttendanceController::class, 'dashboard'])->name('dashboard');
            Route::get('/all-users-summary', [Modules\Essentials\Http\Controllers\EnhancedAttendanceController::class, 'getAllUsersSummary'])->name('all_users_summary');
            Route::get('/user-summary/{user}', [Modules\Essentials\Http\Controllers\EnhancedAttendanceController::class, 'userAttendanceSummary'])->name('user_summary');
            Route::get('/employee-management', [Modules\Essentials\Http\Controllers\EnhancedAttendanceController::class, 'employeeManagement'])->name('employee_management');
            Route::post('/store-employee-settings', [Modules\Essentials\Http\Controllers\EnhancedAttendanceController::class, 'storeEmployeeSettings'])->name('store_employee_settings');
            Route::get('/get-employee-settings/{employee}', [Modules\Essentials\Http\Controllers\EnhancedAttendanceController::class, 'getEmployeeSettings'])->name('get_employee_settings');
            Route::delete('/delete-employee-settings/{employee}', [Modules\Essentials\Http\Controllers\EnhancedAttendanceController::class, 'deleteEmployeeSettings'])->name('delete_employee_settings');
            Route::get('/get-users-for-dropdown', [Modules\Essentials\Http\Controllers\EnhancedAttendanceController::class, 'getUsersForDropdown'])->name('users_dropdown');
        });

        // Payroll management
        Route::get('/employees-by-location', [Modules\Essentials\Http\Controllers\PayrollController::class, 'getEmployeesBasedOnLocation'])->name('payroll.location_employees');
        Route::get('/my-payrolls', [Modules\Essentials\Http\Controllers\PayrollController::class, 'getMyPayrolls'])->name('payroll.my_payrolls');
        Route::get('/get-allowance-deduction-row', [Modules\Essentials\Http\Controllers\PayrollController::class, 'getAllowanceAndDeductionRow'])->name('payroll.allowance_deduction_row');
        Route::get('/payroll-group-datatable', [Modules\Essentials\Http\Controllers\PayrollController::class, 'payrollGroupDatatable'])->name('payroll.group_datatable');
        Route::get('/view/{payroll}/payroll-group', [Modules\Essentials\Http\Controllers\PayrollController::class, 'viewPayrollGroup'])->name('payroll.view_group');
        Route::get('/edit/{payroll}/payroll-group', [Modules\Essentials\Http\Controllers\PayrollController::class, 'getEditPayrollGroup'])->name('payroll.edit_group');
        Route::post('/update-payroll-group', [Modules\Essentials\Http\Controllers\PayrollController::class, 'getUpdatePayrollGroup'])->name('payroll.update_group');
        Route::get('/payroll-group/{payroll}/add-payment', [Modules\Essentials\Http\Controllers\PayrollController::class, 'addPayment'])->name('payroll.add_payment');
        Route::post('/post-payment-payroll-group', [Modules\Essentials\Http\Controllers\PayrollController::class, 'postAddPayment'])->name('payroll.post_payment');
        Route::resource('/payroll', Modules\Essentials\Http\Controllers\PayrollController::class);
        
        // Holiday management
        Route::resource('/holiday', Modules\Essentials\Http\Controllers\EssentialsHolidayController::class);

        // Shift management
        Route::get('/shift/assign-users/{shift}', [Modules\Essentials\Http\Controllers\ShiftController::class, 'getAssignUsers'])->name('shift.assign_users');
        Route::post('/shift/assign-users', [Modules\Essentials\Http\Controllers\ShiftController::class, 'postAssignUsers'])->name('shift.post_assign_users');
        Route::resource('/shift', Modules\Essentials\Http\Controllers\ShiftController::class);
        
        // Sales targets
        Route::get('/sales-target', [Modules\Essentials\Http\Controllers\SalesTargetController::class, 'index'])->name('sales_target.index');
        Route::get('/set-sales-target/{user}', [Modules\Essentials\Http\Controllers\SalesTargetController::class, 'setSalesTarget'])->name('sales_target.set');
        Route::post('/save-sales-target', [Modules\Essentials\Http\Controllers\SalesTargetController::class, 'saveSalesTarget'])->name('sales_target.save');
    });
});