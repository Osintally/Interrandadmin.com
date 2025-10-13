<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\User;

class AssignPayrollPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payroll:assign-permissions {--role=} {--user=} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign all payroll approval permissions to a role or user';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
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

        if ($this->option('all')) {
            $this->assignToAllAdminRoles($allPayrollPermissions);
        } elseif ($roleName = $this->option('role')) {
            $this->assignToRole($roleName, $allPayrollPermissions);
        } elseif ($userId = $this->option('user')) {
            $this->assignToUser($userId, $allPayrollPermissions);
        } else {
            $this->error('Please specify --role, --user, or --all option');
            $this->info('Usage examples:');
            $this->info('  php artisan payroll:assign-permissions --role=superadmin');
            $this->info('  php artisan payroll:assign-permissions --user=1');
            $this->info('  php artisan payroll:assign-permissions --all');
        }

        return Command::SUCCESS;
    }

    private function assignToRole($roleName, $permissions)
    {
        $businessIds = \App\Business::pluck('id')->toArray();
        
        foreach ($businessIds as $businessId) {
            $role = Role::where('name', $roleName)
                       ->where('business_id', $businessId)
                       ->first();
            
            if ($role) {
                $this->info("Assigning permissions to role: {$roleName} (Business: {$businessId})");
                
                foreach ($permissions as $permissionName) {
                    $permission = Permission::where('name', $permissionName)->first();
                    if ($permission && !$role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                        $this->line("  ✓ Added: {$permissionName}");
                    }
                }
            } else {
                $this->warn("Role '{$roleName}' not found for business {$businessId}");
            }
        }
    }

    private function assignToUser($userId, $permissions)
    {
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return;
        }

        $this->info("Assigning permissions to user: {$user->user_full_name} (ID: {$userId})");
        
        foreach ($permissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission && !$user->hasPermissionTo($permission)) {
                $user->givePermissionTo($permission);
                $this->line("  ✓ Added: {$permissionName}");
            }
        }
    }

    private function assignToAllAdminRoles($permissions)
    {
        $adminRoles = ['superadmin', 'admin', 'manager', 'hr_manager'];
        $businessIds = \App\Business::pluck('id')->toArray();
        
        foreach ($businessIds as $businessId) {
            $this->info("Processing business ID: {$businessId}");
            
            foreach ($adminRoles as $roleName) {
                $role = Role::where('name', $roleName)
                           ->where('business_id', $businessId)
                           ->first();
                
                if ($role) {
                    $this->info("  Found role: {$roleName}");
                    
                    foreach ($permissions as $permissionName) {
                        $permission = Permission::where('name', $permissionName)->first();
                        if ($permission && !$role->hasPermissionTo($permission)) {
                            $role->givePermissionTo($permission);
                            $this->line("    ✓ Added: {$permissionName}");
                        }
                    }
                }
            }
        }
    }
}
