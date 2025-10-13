<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignPayrollPermissionsToSuperadminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the first business ID (assuming there's at least one business)
        $businessId = \App\Business::first()->id ?? 1;
        
        // Find or create superadmin role
        $superadminRole = Role::firstOrCreate(
            ['name' => 'superadmin', 'business_id' => $businessId],
            ['guard_name' => 'web']
        );

        // Get all payroll approval permissions
        $payrollPermissions = [
            'payroll.submit_for_review',
            'payroll.review', 
            'payroll.reject',
            'payroll.view_approval_status',
            'payroll.export',
            'payroll.approve', // This might already exist
            'payroll.edit',    // This might already exist
            'payroll.delete',  // This might already exist
            'payroll.view_all' // This might already exist
        ];

        // Assign all permissions to superadmin role
        foreach ($payrollPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission && !$superadminRole->hasPermissionTo($permission)) {
                $superadminRole->givePermissionTo($permission);
            }
        }

        $this->command->info('All payroll approval permissions assigned to superadmin role!');
        
        // Also assign to any existing admin role if it exists
        $adminRole = Role::where('name', 'admin')->where('business_id', $businessId)->first();
        if ($adminRole) {
            foreach ($payrollPermissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission && !$adminRole->hasPermissionTo($permission)) {
                    $adminRole->givePermissionTo($permission);
                }
            }
            $this->command->info('All payroll approval permissions also assigned to admin role!');
        }
    }
}
