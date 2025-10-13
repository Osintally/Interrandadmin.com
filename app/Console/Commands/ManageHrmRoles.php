<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use Spatie\Permission\Models\Role;
use App\Utils\HrmAccessUtil;

class ManageHrmRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hrm:manage-roles 
                            {action : The action to perform (assign|remove|list|check)}
                            {username? : The username to perform action on}
                            {role? : The role to assign/remove (admin|accountant|hr_manager|gm|gmd|hr|human_resources)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage HRM roles for users';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $action = $this->argument('action');
        $username = $this->argument('username');
        $role = $this->argument('role');

        switch ($action) {
            case 'assign':
                return $this->assignRole($username, $role);
            case 'remove':
                return $this->removeRole($username, $role);
            case 'list':
                return $this->listUsersWithHrmAccess();
            case 'check':
                return $this->checkUserAccess($username);
            default:
                $this->error('Invalid action. Use: assign, remove, list, or check');
                return 1;
        }
    }

    /**
     * Assign HRM role to a user
     */
    private function assignRole($username, $role)
    {
        if (!$username || !$role) {
            $this->error('Username and role are required for assign action');
            return 1;
        }

        $user = User::where('username', $username)->first();
        if (!$user) {
            $this->error("User with username '{$username}' not found");
            return 1;
        }

        $businessId = $user->business_id;
        $roleName = $this->getRoleName($role, $businessId);

        if (!$roleName) {
            $this->error("Invalid role '{$role}'. Use: admin, accountant, hr_manager, gm, gmd, hr, or human_resources");
            return 1;
        }

        // Create role if it doesn't exist
        Role::firstOrCreate([
            'name' => $roleName,
            'business_id' => $businessId,
            'guard_name' => 'web'
        ]);

        // Assign role to user
        $user->assignRole($roleName);

        $this->info("Successfully assigned '{$roleName}' role to user '{$username}'");
        return 0;
    }

    /**
     * Remove HRM role from a user
     */
    private function removeRole($username, $role)
    {
        if (!$username || !$role) {
            $this->error('Username and role are required for remove action');
            return 1;
        }

        $user = User::where('username', $username)->first();
        if (!$user) {
            $this->error("User with username '{$username}' not found");
            return 1;
        }

        $businessId = $user->business_id;
        $roleName = $this->getRoleName($role, $businessId);

        if (!$roleName) {
            $this->error("Invalid role '{$role}'. Use: admin, accountant, hr_manager, gm, gmd, hr, or human_resources");
            return 1;
        }

        // Remove role from user
        $user->removeRole($roleName);

        $this->info("Successfully removed '{$roleName}' role from user '{$username}'");
        return 0;
    }

    /**
     * List all users with HRM access
     */
    private function listUsersWithHrmAccess()
    {
        $this->info('Users with HRM Access:');
        $this->line('');

        $users = User::all();
        $hrmUsers = [];

        foreach ($users as $user) {
            if (HrmAccessUtil::userCanAccessHrm($user, $user->business_id)) {
                $roles = $user->roles->pluck('name')->toArray();
                $hrmUsers[] = [
                    'username' => $user->username,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'business_id' => $user->business_id,
                    'roles' => implode(', ', $roles)
                ];
            }
        }

        if (empty($hrmUsers)) {
            $this->warn('No users found with HRM access');
            return 0;
        }

        $headers = ['Username', 'Name', 'Business ID', 'Roles'];
        $this->table($headers, $hrmUsers);

        return 0;
    }

    /**
     * Check if a user has HRM access
     */
    private function checkUserAccess($username)
    {
        if (!$username) {
            $this->error('Username is required for check action');
            return 1;
        }

        $user = User::where('username', $username)->first();
        if (!$user) {
            $this->error("User with username '{$username}' not found");
            return 1;
        }

        $hasAccess = HrmAccessUtil::userCanAccessHrm($user, $user->business_id);
        $roles = $user->roles->pluck('name')->toArray();

        $this->info("User: {$username}");
        $this->info("Name: {$user->first_name} {$user->last_name}");
        $this->info("Business ID: {$user->business_id}");
        $this->info("Roles: " . implode(', ', $roles));
        $this->info("HRM Access: " . ($hasAccess ? 'YES' : 'NO'));

        return 0;
    }

    /**
     * Get the full role name
     */
    private function getRoleName($role, $businessId)
    {
        switch ($role) {
            case 'admin':
                return 'Admin#' . $businessId;
            case 'accountant':
                return 'Accountant#' . $businessId;
            case 'hr_manager':
                return 'HR Manager#' . $businessId;
            case 'gm':
                return 'GM#' . $businessId;
            case 'gmd':
                return 'GMD#' . $businessId;
            case 'hr':
                return 'HR#' . $businessId;
            case 'human_resources':
                return 'Human Resources#' . $businessId;
            default:
                return null;
        }
    }
}
