<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\User;

class SetupHrmPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hrm:setup-permissions {--user-id=1 : User ID to assign permissions to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup HRM permissions for salary, payroll, attendance and performance management';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        
        $this->info("Setting up HRM permissions for user ID: $userId");
        
        // Get all HRM permissions
        $permissions = Permission::whereIn('name', [
            'salary.view_all', 'salary.view_own', 'salary.create', 'salary.edit', 'salary.delete',
            'salary.import', 'salary.export', 'salary.approve',
            'attendance.view_all', 'attendance.view_own', 'attendance.create', 'attendance.edit',
            'attendance.delete', 'attendance.clock_in_out', 'attendance.manage_settings',
            'payroll.view_all', 'payroll.view_own', 'payroll.create', 'payroll.edit',
            'payroll.delete', 'payroll.process', 'payroll.approve', 'payroll.manage_groups',
            'performance.view_all', 'performance.view_own', 'performance.create', 'performance.edit',
            'performance.delete', 'performance.approve', 'performance.manage_templates', 'performance.view_reports'
        ])->get();
        
        $this->info("Found {$permissions->count()} HRM permissions");
        
        // Find user
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID $userId not found!");
            return 1;
        }
        
        $this->info("Found user: {$user->first_name} {$user->last_name}");
        
        // Assign permissions directly to user
        $user->givePermissionTo($permissions);
        
        $this->info("✅ Successfully assigned all HRM permissions to user!");
        
        // Try to create/update Admin role with business_id
        try {
            $businessId = $user->business_id ?? 1;
            $adminRole = Role::firstOrCreate([
                'name' => 'Admin',
                'business_id' => $businessId
            ]);
            $adminRole->givePermissionTo($permissions);
            
            // Assign admin role to user if not already assigned
            if (!$user->hasRole('Admin')) {
                $user->assignRole('Admin');
                $this->info("✅ Assigned Admin role to user");
            }
        } catch (\Exception $e) {
            $this->warn("Could not create Admin role (business constraint), but user permissions are set directly");
        }
        
        $this->info("✅ Setup complete! User can now access all HRM modules");
        
        return 0;
    }
}
