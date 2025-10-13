<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PayrollApprovalPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'payroll.submit_for_review' => 'Submit Payroll for Review',
            'payroll.review' => 'Review Payroll Records',
            'payroll.reject' => 'Reject Payroll Records',
            'payroll.view_approval_status' => 'View Approval Status',
            'payroll.export' => 'Export Payroll Data'
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        $this->command->info('Payroll approval permissions created successfully!');
    }
}
