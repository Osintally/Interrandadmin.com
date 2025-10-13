<?php

namespace Modules\Essentials\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Category;
use App\User;
use App\Business;

class HRDepartmentSeeder extends Seeder
{
    public function run()
    {
        // Get the first business and a user to use as creator
        $business = Business::first();
        $user = User::where('business_id', $business->id)->first();
        
        if (!$business || !$user) {
            echo "No business or user found. Skipping seeder.\n";
            return;
        }

        $departments = [
            [
                'name' => 'Sales',
                'short_code' => 'SALES',
                'description' => 'Sales and Business Development Department'
            ],
            [
                'name' => 'Marketing', 
                'short_code' => 'MKT',
                'description' => 'Marketing and Communications Department'
            ],
            [
                'name' => 'Information Technology',
                'short_code' => 'IT',
                'description' => 'Information Technology and Development Department'
            ],
            [
                'name' => 'Customer Service',
                'short_code' => 'CS',
                'description' => 'Customer Service and Support Department'
            ],
            [
                'name' => 'Human Resources',
                'short_code' => 'HR', 
                'description' => 'Human Resources and People Operations Department'
            ],
            [
                'name' => 'Finance',
                'short_code' => 'FIN',
                'description' => 'Finance and Accounting Department'
            ],
            [
                'name' => 'Operations',
                'short_code' => 'OPS',
                'description' => 'Operations and Administration Department'
            ]
        ];

        foreach ($departments as $deptData) {
            $existingDept = Category::where('business_id', $business->id)
                ->where('category_type', 'hrm_department')
                ->where('name', $deptData['name'])
                ->first();

            if (!$existingDept) {
                Category::create([
                    'business_id' => $business->id,
                    'name' => $deptData['name'],
                    'short_code' => $deptData['short_code'],
                    'category_type' => 'hrm_department',
                    'description' => $deptData['description'],
                    'parent_id' => 0,
                    'created_by' => $user->id
                ]);
                
                echo "Created department: {$deptData['name']}\n";
            } else {
                echo "Department already exists: {$deptData['name']}\n";
            }
        }
    }
}