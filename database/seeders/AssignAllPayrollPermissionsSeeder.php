<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignAllPayrollPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all business IDs
        $businessIds = \App\Business::pluck('id')->toArray();
        
        if (empty($businessIds)) {
            $this->command->warn('No businesses found. Skipping permission assignment.');
            return;
        }

        // All payroll permissions
        $allPayrollPermissions = [
            'payroll.submit_for_review',
            'payroll.review', 
            'payroll.reject',
            'payroll.view_approval_status',
            'payroll.export',
            'payroll.approve',
            'payroll.edit',
            'payroll.delete',
            'payroll.view_all'
        ];

        // Admin roles that should have all permissions
        $adminRoles = ['superadmin', 'admin', 'manager', 'hr_manager'];

        foreach ($businessIds as $businessId) {
            $this->command->info("Processing business ID: {$businessId}");
            
            foreach ($adminRoles as $roleName) {
                $role = Role::where('name', $roleName)
                           ->where('business_id', $businessId)
                           ->first();
                
                if ($role) {
                    $this->command->info("  Found role: {$roleName}");
                    
                    foreach ($allPayrollPermissions as $permissionName) {
                        $permission = Permission::where('name', $permissionName)->first();
                        if ($permission && !$role->hasPermissionTo($permission)) {
                            $role->givePermissionTo($permission);
                            $this->command->info("    Added permission: {$permissionName}");
                        }
                    }
                } else {
                    $this->command->warn("  Role not found: {$roleName}");
                }
            }
        }

        $this->command->info('Payroll permissions assignment completed!');
    }
}
