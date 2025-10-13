<?php

namespace App\Utils;

use Illuminate\Support\Facades\Auth;

class HrmAccessUtil
{
    /**
     * Check if the current user has HRM access
     * Admin, Accountant, HR Manager, GM/GMD, and Superadmin can access HRM
     * This controls access to HRM Management tab and HRM Dashboard only
     * Essentials features (todo, document, memo, knowledge base) are available to all employees
     *
     * @return bool
     */
    public static function canAccessHrm()
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        $businessId = session('business.id');
        
        // Check if user is Superadmin
        $is_superadmin = in_array(
            strtolower($user->username), 
            explode(',', strtolower(config('constants.administrator_usernames')))
        );
        
        if ($is_superadmin) {
            return true;
        }
        
        // Check if user has Admin role for current business
        $is_admin = $user->hasRole('Admin#' . $businessId);
        
        if ($is_admin) {
            return true;
        }
        
        // Check if user has Accountant role for current business
        $is_accountant = $user->hasRole('Accountant#' . $businessId);
        
        if ($is_accountant) {
            return true;
        }
        
        // Check if user has HR-related roles
        $is_hr_role = $user->hasRole('HR Manager#' . $businessId) || 
                      $user->hasRole('GM#' . $businessId) ||
                      $user->hasRole('GMD#' . $businessId) ||
                      $user->hasRole('HR#' . $businessId) ||
                      $user->hasRole('Human Resources#' . $businessId);
        
        if ($is_hr_role) {
            return true;
        }
        
        // Check if user has specific HRM permissions (for custom roles)
        $has_hrm_permissions = $user->can('essentials.manage_payroll') || 
                              $user->can('essentials.view_payroll') ||
                              $user->can('payroll.view_all') ||
                              $user->can('payroll.manage_groups') ||
                              $user->can('attendance.manage_settings') ||
                              $user->can('salary.view_all') ||
                              $user->can('salary.manage');
        
        return $has_hrm_permissions;
    }
    
    /**
     * Get the list of roles that can access HRM
     *
     * @return array
     */
    public static function getHrmAccessRoles()
    {
        return [
            'Superadmin',
            'Admin',
            'Accountant',
            'HR Manager',
            'GM',
            'GMD',
            'HR',
            'Human Resources'
        ];
    }
    
    /**
     * Get the list of permissions that grant HRM access
     *
     * @return array
     */
    public static function getHrmAccessPermissions()
    {
        return [
            'essentials.manage_payroll',
            'essentials.view_payroll',
            'payroll.view_all',
            'payroll.manage_groups',
            'attendance.manage_settings',
            'salary.view_all',
            'salary.manage'
        ];
    }
    
    /**
     * Check if a specific user has HRM access
     *
     * @param \App\User $user
     * @param int $businessId
     * @return bool
     */
    public static function userCanAccessHrm($user, $businessId = null)
    {
        if (!$user) {
            return false;
        }
        
        $businessId = $businessId ?? session('business.id');
        
        // Check if user is Superadmin
        $is_superadmin = in_array(
            strtolower($user->username), 
            explode(',', strtolower(config('constants.administrator_usernames')))
        );
        
        if ($is_superadmin) {
            return true;
        }
        
        // Check if user has Admin role for the business
        $is_admin = $user->hasRole('Admin#' . $businessId);
        
        if ($is_admin) {
            return true;
        }
        
        // Check if user has Accountant role for the business
        $is_accountant = $user->hasRole('Accountant#' . $businessId);
        
        if ($is_accountant) {
            return true;
        }
        
        // Check if user has HR-related roles
        $is_hr_role = $user->hasRole('HR Manager#' . $businessId) || 
                      $user->hasRole('GM#' . $businessId) ||
                      $user->hasRole('GMD#' . $businessId) ||
                      $user->hasRole('HR#' . $businessId) ||
                      $user->hasRole('Human Resources#' . $businessId);
        
        if ($is_hr_role) {
            return true;
        }
        
        // Check if user has specific HRM permissions (for custom roles)
        $has_hrm_permissions = $user->can('essentials.manage_payroll') || 
                              $user->can('essentials.view_payroll') ||
                              $user->can('payroll.view_all') ||
                              $user->can('payroll.manage_groups') ||
                              $user->can('attendance.manage_settings') ||
                              $user->can('salary.view_all') ||
                              $user->can('salary.manage');
        
        return $has_hrm_permissions;
    }
}
