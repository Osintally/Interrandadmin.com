<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enhanced Attendance Logs - Skip if already exists
        if (!Schema::hasTable('attendance_logs')) {
            Schema::create('attendance_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->date('attendance_date')->index();
                $table->datetime('clock_in')->nullable();
                $table->datetime('clock_out')->nullable();
                $table->decimal('total_hours', 5, 2)->default(0);
                $table->enum('status', ['present', 'absent', 'half_day', 'sick', 'vacation', 'holiday'])->default('absent');
                
                // Enhanced late tracking
                $table->enum('late_status', ['no', 'low_late', 'late', 'very_late'])->default('no');
                $table->integer('late_minutes')->default(0);
                
                // Additional tracking
                $table->boolean('early_leave')->default(false);
                $table->integer('early_leave_minutes')->default(0);
                $table->boolean('overtime')->default(false);
                $table->integer('overtime_minutes')->default(0);
                
                // Location tracking
                $table->string('location_check_in', 200)->nullable();
                $table->string('location_check_out', 200)->nullable();
                $table->text('notes')->nullable();
                
                $table->timestamps();
                
                // Indexes for performance
                $table->index(['business_id', 'attendance_date']);
                $table->index(['user_id', 'attendance_date']);
                $table->index(['business_id', 'user_id', 'attendance_date']);
            });
        }

        // Enhanced Attendance Settings
        if (!Schema::hasTable('attendance_settings')) {
            Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->unique();
            
            // Time settings
            $table->time('start_time')->default('08:00:00');
            $table->time('end_time')->default('16:00:00');
            $table->integer('grace_minutes')->default(15);
            $table->integer('late_threshold_minutes')->default(5);
            $table->integer('very_late_threshold_minutes')->default(30);
            $table->integer('early_leave_threshold_minutes')->default(30);
            
            // Working days configuration
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
            
            $table->timestamps();
            });
        }

        // Employee Salary Records
        if (!Schema::hasTable('employee_salaries')) {
            Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            
            // Salary details
            $table->decimal('basic_salary', 12, 2);
            $table->decimal('house_allowance', 12, 2)->default(0);
            $table->decimal('transport_allowance', 12, 2)->default(0);
            $table->decimal('meal_allowance', 12, 2)->default(0);
            $table->decimal('medical_allowance', 12, 2)->default(0);
            $table->decimal('other_allowances', 12, 2)->default(0);
            
            // Deductions
            $table->decimal('tax_deduction', 12, 2)->default(0);
            $table->decimal('pension_deduction', 12, 2)->default(0);
            $table->decimal('insurance_deduction', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            
            // Employment details
            $table->string('employee_id', 50)->nullable();
            $table->string('department', 100)->nullable();
            $table->string('position', 100)->nullable();
            $table->date('salary_effective_date');
            $table->date('salary_end_date')->nullable();
            
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['business_id', 'user_id']);
            $table->index(['business_id', 'active']);
            });
        }

        // Payroll Records
        if (!Schema::hasTable('payroll_records')) {
            Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->index();
            $table->unsignedBigInteger('user_id')->index();
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
            $table->integer('days_low_late')->default(0);
            $table->integer('days_very_late')->default(0);
            $table->integer('days_early_leave')->default(0);
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
            $table->unsignedBigInteger('calculated_by')->nullable();
            $table->datetime('calculated_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->datetime('approved_at')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['business_id', 'payroll_date']);
            $table->index(['business_id', 'status']);
            $table->index(['user_id', 'payroll_date']);
            $table->unique(['business_id', 'user_id', 'payroll_date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
        Schema::dropIfExists('employee_salaries');
        Schema::dropIfExists('attendance_settings');
        Schema::dropIfExists('attendance_logs');
    }
};