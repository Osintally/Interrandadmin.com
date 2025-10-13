<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class AddEnhancedPerformancePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $performancePermissions = [
            // Template Management
            'performance.template.view_all' => 'View all performance templates',
            'performance.template.view_own' => 'View own performance templates',
            'performance.template.view_department' => 'View department performance templates',
            'performance.template.add' => 'Create performance templates',
            'performance.template.edit' => 'Edit performance templates',
            'performance.template.delete' => 'Delete performance templates',
            'performance.template.publish' => 'Publish/unpublish templates',
            'performance.template.configure_kpi' => 'Configure KPI parameters',
            
            // Performance Reports
            'performance.report.view_all' => 'View all performance reports',
            'performance.report.view_own' => 'View own performance reports',
            'performance.report.view_department' => 'View department performance reports',
            'performance.report.submit' => 'Submit performance reports',
            'performance.report.edit_own' => 'Edit own performance reports',
            'performance.report.save_draft' => 'Save performance reports as draft',
            'performance.report.approve' => 'Approve performance reports',
            'performance.report.reject' => 'Reject performance reports',
            'performance.report.export' => 'Export performance reports',
            
            // KPI and Analytics
            'performance.kpi.view_all' => 'View all KPI data',
            'performance.kpi.view_department' => 'View department KPI data',
            'performance.kpi.view_own' => 'View own KPI data',
            'performance.kpi.configure' => 'Configure KPI parameters and formulas',
            'performance.kpi.set_targets' => 'Set KPI targets and thresholds',
            'performance.kpi.benchmarks' => 'Manage KPI benchmarks',
            
            // Dashboard Access
            'performance.dashboard.admin' => 'Access admin performance dashboard',
            'performance.dashboard.manager' => 'Access manager performance dashboard',
            'performance.dashboard.employee' => 'Access employee performance dashboard',
            'performance.dashboard.analytics' => 'Access advanced performance analytics',
            'performance.dashboard.realtime' => 'View real-time performance updates',
            
            // Goals and Reviews
            'performance.goals.view_all' => 'View all performance goals',
            'performance.goals.view_department' => 'View department performance goals',
            'performance.goals.view_own' => 'View own performance goals',
            'performance.goals.set_department' => 'Set department performance goals',
            'performance.goals.set_individual' => 'Set individual performance goals',
            'performance.review.conduct' => 'Conduct performance reviews',
            'performance.review.receive' => 'Receive performance reviews',
        ];

        foreach ($performancePermissions as $name => $description) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web'
            ], [
                'name' => $name,
                'guard_name' => 'web'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $performancePermissions = [
            'performance.template.view_all',
            'performance.template.view_own',
            'performance.template.view_department',
            'performance.template.add',
            'performance.template.edit',
            'performance.template.delete',
            'performance.template.publish',
            'performance.template.configure_kpi',
            'performance.report.view_all',
            'performance.report.view_own',
            'performance.report.view_department',
            'performance.report.submit',
            'performance.report.edit_own',
            'performance.report.save_draft',
            'performance.report.approve',
            'performance.report.reject',
            'performance.report.export',
            'performance.kpi.view_all',
            'performance.kpi.view_department',
            'performance.kpi.view_own',
            'performance.kpi.configure',
            'performance.kpi.set_targets',
            'performance.kpi.benchmarks',
            'performance.dashboard.admin',
            'performance.dashboard.manager',
            'performance.dashboard.employee',
            'performance.dashboard.analytics',
            'performance.dashboard.realtime',
            'performance.goals.view_all',
            'performance.goals.view_department',
            'performance.goals.view_own',
            'performance.goals.set_department',
            'performance.goals.set_individual',
            'performance.review.conduct',
            'performance.review.receive',
        ];

        foreach ($performancePermissions as $permission) {
            Permission::where('name', $permission)->delete();
        }
    }
}
