<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permissions = [
            // Salary Management Permissions
            'salary.view_all',
            'salary.view_own',
            'salary.create',
            'salary.edit',
            'salary.delete',
            'salary.import',
            'salary.export',
            'salary.approve',
            
            // Modern Attendance Permissions
            'attendance.view_all',
            'attendance.view_own',
            'attendance.create',
            'attendance.edit',
            'attendance.delete',
            'attendance.clock_in_out',
            'attendance.manage_settings',
            
            // Payroll Management Permissions
            'payroll.view_all',
            'payroll.view_own',
            'payroll.create',
            'payroll.edit',
            'payroll.delete',
            'payroll.process',
            'payroll.approve',
            'payroll.manage_groups',
            
            // Performance Management Permissions (updating existing)
            'performance.view_all',
            'performance.view_own',
            'performance.create',
            'performance.edit',
            'performance.delete',
            'performance.approve',
            'performance.manage_templates',
            'performance.view_reports',
            
            // HRM Settings and Administration
            'hrm.manage_settings',
            'hrm.view_dashboard',
            'hrm.manage_departments',
            'hrm.manage_positions',
            'hrm.manage_shifts',
            
            // Role and Permission Management
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'permissions.assign',
            'permissions.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permissions = [
            'salary.view_all', 'salary.view_own', 'salary.create', 'salary.edit', 'salary.delete',
            'salary.import', 'salary.export', 'salary.approve',
            'attendance.view_all', 'attendance.view_own', 'attendance.create', 'attendance.edit',
            'attendance.delete', 'attendance.clock_in_out', 'attendance.manage_settings',
            'payroll.view_all', 'payroll.view_own', 'payroll.create', 'payroll.edit',
            'payroll.delete', 'payroll.process', 'payroll.approve', 'payroll.manage_groups',
            'performance.view_all', 'performance.view_own', 'performance.create', 'performance.edit',
            'performance.delete', 'performance.approve', 'performance.manage_templates', 'performance.view_reports',
            'hrm.manage_settings', 'hrm.view_dashboard', 'hrm.manage_departments',
            'hrm.manage_positions', 'hrm.manage_shifts',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'permissions.assign', 'permissions.view'
        ];

        foreach ($permissions as $permission) {
            Permission::where('name', $permission)->delete();
        }
    }
};
