<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\User;

class AssignSalaryPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary:assign-permissions {--role=Admin : Role to assign permissions to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign salary management permissions to a role';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $roleName = $this->option('role');
        
        $this->info("Assigning salary permissions to '$roleName' role...");
        
        // Get all salary permissions
        $salaryPermissions = Permission::where('name', 'like', 'salary.%')->get();
        $this->info("Found {$salaryPermissions->count()} salary permissions:");
        foreach ($salaryPermissions as $permission) {
            $this->line("  - {$permission->name}");
        }
        
        // Find or create the role
        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $role = Role::create(['name' => $roleName]);
            $this->info("Created new role: $roleName");
        } else {
            $this->info("Found existing role: $roleName");
        }
        
        // Assign permissions to role
        $role->givePermissionTo($salaryPermissions);
        
        $this->info("✅ Successfully assigned all salary permissions to '$roleName' role!");
        
        // Show users with this role
        $users = $role->users;
        if ($users->count() > 0) {
            $this->info("\nUsers with '$roleName' role:");
            foreach ($users as $user) {
                $this->line("  - {$user->first_name} {$user->last_name} (ID: {$user->id})");
            }
        } else {
            $this->warn("\nNo users assigned to '$roleName' role yet.");
            $this->info("To assign this role to a user, run:");
            $this->line("php artisan tinker --execute=\"\$user = App\\User::find(USER_ID); \$user->assignRole('$roleName');\"");
        }
        
        return 0;
    }
}
