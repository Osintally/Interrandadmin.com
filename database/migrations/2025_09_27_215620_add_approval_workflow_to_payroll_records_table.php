<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_records', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('payroll_records', 'approval_status')) {
                $table->enum('approval_status', ['draft', 'submitted_for_review', 'pending_approval', 'approved', 'rejected'])->default('draft')->after('status');
            }
            if (!Schema::hasColumn('payroll_records', 'submitted_by')) {
                $table->unsignedBigInteger('submitted_by')->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('payroll_records', 'reviewed_by')) {
                $table->unsignedBigInteger('reviewed_by')->nullable()->after('submitted_by');
            }
            if (!Schema::hasColumn('payroll_records', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('payroll_records', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_by');
            }
            if (!Schema::hasColumn('payroll_records', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('rejection_reason');
            }
            if (!Schema::hasColumn('payroll_records', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('submitted_at');
            }
            if (!Schema::hasColumn('payroll_records', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }
        });
        
        // Note: Foreign key constraints will be added later if needed
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_records', function (Blueprint $table) {
            // Drop columns if they exist
            $columns_to_drop = [];
            
            if (Schema::hasColumn('payroll_records', 'approval_status')) {
                $columns_to_drop[] = 'approval_status';
            }
            if (Schema::hasColumn('payroll_records', 'submitted_by')) {
                $columns_to_drop[] = 'submitted_by';
            }
            if (Schema::hasColumn('payroll_records', 'reviewed_by')) {
                $columns_to_drop[] = 'reviewed_by';
            }
            if (Schema::hasColumn('payroll_records', 'rejected_by')) {
                $columns_to_drop[] = 'rejected_by';
            }
            if (Schema::hasColumn('payroll_records', 'rejection_reason')) {
                $columns_to_drop[] = 'rejection_reason';
            }
            if (Schema::hasColumn('payroll_records', 'submitted_at')) {
                $columns_to_drop[] = 'submitted_at';
            }
            if (Schema::hasColumn('payroll_records', 'reviewed_at')) {
                $columns_to_drop[] = 'reviewed_at';
            }
            if (Schema::hasColumn('payroll_records', 'rejected_at')) {
                $columns_to_drop[] = 'rejected_at';
            }
            
            if (!empty($columns_to_drop)) {
                $table->dropColumn($columns_to_drop);
            }
        });
    }
};
