<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\User;

class CreateAccountantRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all businesses
        $businesses = \App\Business::all();
        
        foreach ($businesses as $business) {
            $businessId = $business->id;
            
            // Create Accountant role if it doesn't exist
            $accountantRole = Role::firstOrCreate([
                'name' => 'Accountant#' . $businessId,
                'business_id' => $businessId,
                'guard_name' => 'web'
            ]);
            
            // Define permissions for Accountant role
            $accountantPermissions = [
                // Financial and Accounting permissions
                'accounting.access_accounting_module',
                'accounting.manage_accounts',
                'accounting.view_journal',
                'accounting.add_journal',
                'accounting.edit_journal',
                'accounting.map_transactions',
                'accounting.view_transfer',
                'accounting.add_transfer',
                'accounting.edit_transfer',
                
                // HRM permissions for payroll and salary management
                'essentials.manage_payroll',
                'essentials.view_payroll',
                'essentials.view_salary',
                'essentials.manage_salary',
                'essentials.edit_salary',
                'payroll.view_all',
                'payroll.manage_groups',
                'payroll.process',
                'payroll.approve',
                'salary.view_all',
                'salary.manage',
                'salary.approve',
                
                // Basic user and contact permissions
                'user.view',
                'contact.view',
                'contact.create',
                'contact.update',
                
                // Dashboard and reporting
                'dashboard.data',
                'report.view',
                
                // Location access
                'access_all_locations'
            ];
            
            // Create permissions if they don't exist
            foreach ($accountantPermissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'web'
                ]);
            }
            
            // Sync permissions to role
            $accountantRole->syncPermissions($accountantPermissions);
            
            $this->command->info("Created Accountant role for business ID: {$businessId}");
        }
        
        $this->command->info('Accountant roles created successfully for all businesses!');
    }
}
