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
        // Disable foreign key checks to avoid constraint issues
        Schema::disableForeignKeyConstraints();
        
        // Drop enhanced attendance tables in the correct order
        Schema::dropIfExists('essentials_employee_payroll');
        Schema::dropIfExists('essentials_attendance_summary');
        Schema::dropIfExists('essentials_employee_settings');
        Schema::dropIfExists('essentials_payroll_settings');
        Schema::dropIfExists('payroll_records');
        Schema::dropIfExists('salary_settings');
        Schema::dropIfExists('attendance_settings');
        Schema::dropIfExists('attendance_logs');

        // Remove any enhanced fields from existing tables if they exist
        if (Schema::hasColumn('essentials_attendances', 'enhanced_status')) {
            Schema::table('essentials_attendances', function (Blueprint $table) {
                $table->dropColumn('enhanced_status');
            });
        }

        if (Schema::hasColumn('essentials_attendances', 'processed_by_enhanced')) {
            Schema::table('essentials_attendances', function (Blueprint $table) {
                $table->dropColumn('processed_by_enhanced');
            });
        }

        if (Schema::hasColumn('essentials_attendances', 'late_minutes')) {
            Schema::table('essentials_attendances', function (Blueprint $table) {
                $table->dropColumn('late_minutes');
            });
        }

        if (Schema::hasColumn('essentials_attendances', 'overtime_minutes')) {
            Schema::table('essentials_attendances', function (Blueprint $table) {
                $table->dropColumn('overtime_minutes');
            });
        }
        
        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This is intentionally left empty as we don't want to restore enhanced attendance
        // If needed, the enhanced attendance system can be reinstalled using the documentation
    }
};