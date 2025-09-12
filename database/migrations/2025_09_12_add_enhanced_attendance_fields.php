<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnhancedAttendanceFields extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Add new fields to existing essentials_attendances table
        Schema::table('essentials_attendances', function (Blueprint $table) {
            // Enhanced tracking fields
            $table->string('attendance_status')->default('present')->after('clock_out_note'); // present, absent, late, early_leave
            $table->time('expected_clock_in')->default('08:00:00')->after('attendance_status');
            $table->time('expected_clock_out')->default('17:00:00')->after('expected_clock_in');
            $table->integer('late_minutes')->default(0)->after('expected_clock_out');
            $table->integer('early_leave_minutes')->default(0)->after('late_minutes');
            $table->decimal('work_hours', 4, 2)->default(0)->after('early_leave_minutes');
            $table->boolean('is_holiday')->default(false)->after('work_hours');
            $table->boolean('is_weekend')->default(false)->after('is_holiday');
            $table->text('admin_notes')->nullable()->after('is_weekend');
            
            // Location tracking enhancements
            $table->string('clock_in_location')->nullable()->after('admin_notes');
            $table->string('clock_out_location')->nullable()->after('clock_in_location');
            
            // Shift relationship (already exists in some versions)
            if (!Schema::hasColumn('essentials_attendances', 'essentials_shift_id')) {
                $table->unsignedInteger('essentials_shift_id')->nullable()->after('business_id');
            }
            
            // Add indexes for better performance
            $table->index(['user_id', 'clock_in_time']);
            $table->index(['attendance_status']);
            $table->index(['is_holiday', 'is_weekend']);
        });

        // Create employee settings table for enhanced management
        Schema::create('essentials_employee_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('business_id');
            $table->string('employee_code')->nullable(); // For matching with external systems
            $table->string('status')->default('active'); // active, inactive, suspended
            $table->time('default_start_time')->default('08:00:00');
            $table->time('default_end_time')->default('17:00:00');
            $table->integer('grace_minutes')->default(15); // Minutes allowed for late arrival
            $table->decimal('base_salary', 10, 2)->default(0);
            $table->boolean('overtime_eligible')->default(false);
            $table->decimal('overtime_rate', 4, 2)->default(1.5);
            $table->json('working_days')->default('[1,2,3,4,5]'); // Monday to Friday
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'business_id']);
            $table->index(['business_id', 'status']);
            $table->index(['employee_code']);
        });

        // Create attendance summary table for performance
        Schema::create('essentials_attendance_summary', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('business_id');
            $table->date('date');
            $table->string('status'); // present, absent, late, partial_day, holiday
            $table->time('clock_in_time')->nullable();
            $table->time('clock_out_time')->nullable();
            $table->decimal('total_hours', 4, 2)->default(0);
            $table->integer('late_minutes')->default(0);
            $table->integer('early_leave_minutes')->default(0);
            $table->boolean('is_holiday')->default(false);
            $table->boolean('is_weekend')->default(false);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'business_id', 'date']);
            $table->index(['business_id', 'date']);
            $table->index(['status']);
        });

        // Create payroll settings table
        Schema::create('essentials_payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            $table->string('name');
            $table->string('type'); // allowance, deduction, bonus
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('calculation_method')->default('fixed'); // fixed, percentage, hourly
            $table->boolean('taxable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['business_id', 'is_active']);
            $table->index(['type']);
        });

        // Create employee payroll components
        Schema::create('essentials_employee_payroll', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('business_id');
            $table->unsignedBigInteger('payroll_setting_id');
            $table->decimal('amount', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('payroll_setting_id')->references('id')->on('essentials_payroll_settings')->onDelete('cascade');
            $table->unique(['user_id', 'payroll_setting_id']);
            $table->index(['business_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('essentials_employee_payroll');
        Schema::dropIfExists('essentials_payroll_settings');
        Schema::dropIfExists('essentials_attendance_summary');
        Schema::dropIfExists('essentials_employee_settings');
        
        Schema::table('essentials_attendances', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'clock_in_time']);
            $table->dropIndex(['attendance_status']);
            $table->dropIndex(['is_holiday', 'is_weekend']);
            
            $table->dropColumn([
                'attendance_status',
                'expected_clock_in',
                'expected_clock_out',
                'late_minutes',
                'early_leave_minutes',
                'work_hours',
                'is_holiday',
                'is_weekend',
                'admin_notes',
                'clock_in_location',
                'clock_out_location'
            ]);
        });
    }
}
