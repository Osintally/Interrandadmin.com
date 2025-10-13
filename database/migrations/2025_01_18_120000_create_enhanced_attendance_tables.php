<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnhancedAttendanceTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Enhanced Attendance Logs with advanced tracking
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id')->index();
            $table->unsignedInteger('user_id')->index();
            $table->date('attendance_date')->index();
            $table->datetime('clock_in')->nullable();
            $table->datetime('clock_out')->nullable();
            $table->decimal('total_hours', 5, 2)->default(0);
            $table->enum('status', ['present', 'absent', 'half_day', 'sick', 'vacation', 'holiday'])->default('absent');
            
            // Enhanced late tracking
            $table->enum('late_status', ['no', 'low_late', 'late', 'very_late'])->default('no');
            $table->string('late_category', 50)->nullable();
            $table->integer('late_minutes')->default(0);
            
            // Additional tracking
            $table->boolean('early_leave')->default(false);
            $table->integer('early_leave_minutes')->default(0);
            $table->boolean('overtime')->default(false);
            $table->integer('overtime_minutes')->default(0);
            
            // Location and device tracking
            $table->string('clock_in_ip', 45)->nullable();
            $table->string('clock_out_ip', 45)->nullable();
            $table->string('clock_in_device', 100)->nullable();
            $table->string('clock_out_device', 100)->nullable();
            $table->decimal('clock_in_lat', 10, 8)->nullable();
            $table->decimal('clock_in_lng', 11, 8)->nullable();
            $table->decimal('clock_out_lat', 10, 8)->nullable();
            $table->decimal('clock_out_lng', 11, 8)->nullable();
            
            // Notes and approvals
            $table->text('notes')->nullable();
            $table->boolean('requires_approval')->default(false);
            $table->boolean('approved')->default(false);
            $table->unsignedInteger('approved_by')->nullable();
            $table->datetime('approved_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['business_id', 'attendance_date']);
            $table->index(['user_id', 'attendance_date']);
            $table->index(['business_id', 'user_id', 'attendance_date']);
            
            // Foreign keys
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        // Enhanced Attendance Settings with more configurations
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id')->unique();
            
            // Time settings
            $table->time('start_time')->default('08:00:00');
            $table->time('end_time')->default('16:00:00');
            $table->integer('grace_minutes')->default(15);
            $table->integer('late_threshold_minutes')->default(5);
            
            // Working days configuration
            $table->json('working_days')->default('[1,2,3,4,5]'); // Mon-Fri
            $table->integer('working_days_per_month')->default(21);
            $table->integer('working_hours_per_day')->default(8);
            
            // Payroll deduction settings
            $table->decimal('absent_deduction_percent', 5, 2)->default(5.00);
            $table->decimal('late_deduction_percent', 5, 2)->default(1.00);
            $table->decimal('low_late_deduction_percent', 5, 2)->default(0.50);
            $table->decimal('very_late_deduction_percent', 5, 2)->default(2.00);
            $table->decimal('early_leave_deduction_percent', 5, 2)->default(1.00);
            
            // Overtime settings
            $table->boolean('overtime_enabled')->default(true);
            $table->decimal('overtime_multiplier', 3, 2)->default(1.50);
            $table->integer('overtime_threshold_minutes')->default(480); // 8 hours
            
            // Notification settings
            $table->boolean('notify_late_arrivals')->default(true);
            $table->boolean('notify_absences')->default(true);
            $table->boolean('notify_early_leaves')->default(true);
            
            // Auto-processing settings
            $table->boolean('auto_process_files')->default(false);
            $table->string('file_processing_schedule', 50)->default('daily');
            $table->time('processing_time')->default('18:00:00');
            
            // Location tracking
            $table->boolean('location_tracking_enabled')->default(false);
            $table->decimal('office_lat', 10, 8)->nullable();
            $table->decimal('office_lng', 11, 8)->nullable();
            $table->integer('location_radius_meters')->default(100);
            
            // Currency and formatting
            $table->string('currency', 10)->default('NGN');
            $table->string('currency_symbol', 10)->default('₦');
            $table->string('date_format', 20)->default('Y-m-d');
            $table->string('time_format', 20)->default('H:i:s');
            
            $table->timestamps();
            
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
        });

        // Employee Salary and Benefits
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id')->index();
            $table->unsignedInteger('user_id')->index();
            
            // Salary details
            $table->decimal('basic_salary', 12, 2);
            $table->decimal('house_allowance', 12, 2)->default(0);
            $table->decimal('transport_allowance', 12, 2)->default(0);
            $table->decimal('meal_allowance', 12, 2)->default(0);
            $table->decimal('medical_allowance', 12, 2)->default(0);
            $table->decimal('other_allowances', 12, 2)->default(0);
            $table->decimal('gross_salary', 12, 2);
            
            // Deductions
            $table->decimal('tax_deduction', 12, 2)->default(0);
            $table->decimal('pension_deduction', 12, 2)->default(0);
            $table->decimal('insurance_deduction', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            
            // Salary type and frequency
            $table->enum('salary_type', ['monthly', 'weekly', 'daily', 'hourly'])->default('monthly');
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->decimal('daily_rate', 8, 2)->nullable();
            
            // Employment details
            $table->string('employee_id', 50)->nullable();
            $table->string('department', 100)->nullable();
            $table->string('position', 100)->nullable();
            $table->date('employment_start_date')->nullable();
            $table->date('salary_effective_date');
            $table->date('salary_end_date')->nullable();
            
            // Bank details
            $table->string('bank_name', 100)->nullable();
            $table->string('account_number', 50)->nullable();
            $table->string('account_name', 100)->nullable();
            
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index(['business_id', 'user_id']);
            $table->index(['business_id', 'active']);
            $table->unique(['business_id', 'user_id', 'salary_effective_date'], 'emp_sal_bus_user_date_unique');
            
            // Foreign keys
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Payroll Records
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id')->index();
            $table->unsignedInteger('user_id')->index();
            $table->unsignedBigInteger('salary_id')->index();
            
            // Payroll period
            $table->date('payroll_date');
            $table->date('period_start');
            $table->date('period_end');
            
            // Attendance summary
            $table->integer('total_working_days');
            $table->integer('days_present');
            $table->integer('days_absent');
            $table->integer('days_late');
            $table->integer('days_low_late');
            $table->integer('days_very_late');
            $table->integer('days_early_leave');
            $table->decimal('total_hours_worked', 8, 2);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            
            // Salary calculations
            $table->decimal('basic_salary', 12, 2);
            $table->decimal('allowances_total', 12, 2)->default(0);
            $table->decimal('overtime_amount', 12, 2)->default(0);
            $table->decimal('gross_amount', 12, 2);
            
            // Deductions
            $table->decimal('attendance_deductions', 12, 2)->default(0);
            $table->decimal('tax_deductions', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            
            // Final amounts
            $table->decimal('net_amount', 12, 2);
            
            // Status and approvals
            $table->enum('status', ['draft', 'calculated', 'approved', 'paid', 'cancelled'])->default('draft');
            $table->unsignedInteger('calculated_by')->nullable();
            $table->datetime('calculated_at')->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->datetime('approved_at')->nullable();
            $table->unsignedInteger('paid_by')->nullable();
            $table->datetime('paid_at')->nullable();
            
            // Payment details
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['business_id', 'payroll_date']);
            $table->index(['business_id', 'status']);
            $table->index(['user_id', 'payroll_date']);
            $table->unique(['business_id', 'user_id', 'payroll_date'], 'payroll_bus_user_date_unique');
            
            // Foreign keys
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('salary_id')->references('id')->on('employee_salaries')->onDelete('cascade');
        });

        // Attendance Analytics and Reports Cache
        Schema::create('attendance_analytics', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id')->index();
            $table->string('metric_type', 100); // 'daily_summary', 'monthly_summary', 'user_performance'
            $table->date('metric_date');
            $table->json('metric_data');
            $table->datetime('calculated_at');
            $table->timestamps();
            
            $table->index(['business_id', 'metric_type', 'metric_date']);
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
        });

        // File Processing Logs
        Schema::create('attendance_file_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id')->index();
            $table->unsignedInteger('uploaded_by')->index();
            $table->string('file_type', 50); // 'user_mapping', 'attendance_data', 'payroll_data'
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path');
            $table->bigInteger('file_size');
            $table->string('file_hash', 64);
            
            // Processing status
            $table->enum('status', ['uploaded', 'processing', 'completed', 'failed'])->default('uploaded');
            $table->text('processing_log')->nullable();
            $table->integer('records_processed')->default(0);
            $table->integer('records_failed')->default(0);
            $table->datetime('processed_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['business_id', 'status']);
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_file_logs');
        Schema::dropIfExists('attendance_analytics');
        Schema::dropIfExists('payroll_records');
        Schema::dropIfExists('employee_salaries');
        Schema::dropIfExists('attendance_settings');
        Schema::dropIfExists('attendance_logs');
    }
}