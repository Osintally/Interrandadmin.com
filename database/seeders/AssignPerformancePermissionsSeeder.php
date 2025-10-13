<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignPerformancePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all performance permissions
        $performancePermissions = Permission::where('name', 'like', 'performance.%')->get();
        
        if ($performancePermissions->isEmpty()) {
            $this->command->warn('No performance permissions found.');
            return;
        }

        // Get admin roles
        $adminRoles = Role::where('name', 'like', '%Admin%')->get();
        
        if ($adminRoles->isEmpty()) {
            $this->command->warn('No admin roles found.');
            return;
        }

        // Assign all performance permissions to admin roles
        foreach ($adminRoles as $role) {
            $role->givePermissionTo($performancePermissions);
            $this->command->info("Assigned performance permissions to role: {$role->name}");
        }

        // Also assign to Human Resources Manager
        $hrRole = Role::where('name', 'like', '%Human Resources%')->first();
        if ($hrRole) {
            $hrRole->givePermissionTo($performancePermissions);
            $this->command->info("Assigned performance permissions to role: {$hrRole->name}");
        }

        // Assign basic permissions to other roles
        $basicPermissions = Permission::whereIn('name', [
            'performance.view_dashboard',
            'performance.dashboard.employee',
            'performance.goals.view_own',
            'performance.kpi.view_own',
            'performance.report.view_own'
        ])->get();

        $otherRoles = Role::whereNotIn('name', ['Admin#1', 'Admin#2', 'Admin#3', 'Human Resources Manager#1'])->get();
        
        foreach ($otherRoles as $role) {
            $role->givePermissionTo($basicPermissions);
            $this->command->info("Assigned basic performance permissions to role: {$role->name}");
        }

        $this->command->info('Performance permissions assignment completed!');
    }
}
