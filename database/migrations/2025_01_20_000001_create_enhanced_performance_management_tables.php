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
        // Create performance KPIs table
        Schema::create('performance_kpis', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('department_id')->nullable();
            $table->decimal('weight', 3, 1)->default(5.0);
            $table->decimal('target_value', 5, 2)->nullable();
            $table->enum('calculation_method', ['direct_score', 'weighted_average', 'formula', 'percentage'])->default('direct_score');
            $table->text('scoring_formula')->nullable();
            $table->timestamps();
            
            $table->foreign('department_id')->references('id')->on('categories')->onDelete('set null');
            $table->index(['department_id']);
        });

        // Create template KPI mapping table
        Schema::create('template_kpi_mapping', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id');
            $table->string('field_name');
            $table->unsignedBigInteger('kpi_id');
            $table->decimal('weight', 3, 1)->default(5.0);
            $table->text('scoring_formula')->nullable();
            $table->timestamps();
            
            $table->foreign('template_id')->references('id')->on('essentials_performance_templates')->onDelete('cascade');
            $table->foreign('kpi_id')->references('id')->on('performance_kpis')->onDelete('cascade');
            $table->unique(['template_id', 'field_name']);
            $table->index(['template_id', 'kpi_id']);
        });

        // Create KPI thresholds table
        Schema::create('kpi_thresholds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kpi_id');
            $table->decimal('excellent_min', 5, 2)->default(9.0);
            $table->decimal('good_min', 5, 2)->default(7.5);
            $table->decimal('needs_improvement_min', 5, 2)->default(6.0);
            $table->timestamps();
            
            $table->foreign('kpi_id')->references('id')->on('performance_kpis')->onDelete('cascade');
            $table->unique('kpi_id');
        });

        // Create KPI scores table
        Schema::create('kpi_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('report_id');
            $table->unsignedBigInteger('kpi_id');
            $table->text('field_value')->nullable();
            $table->decimal('calculated_score', 4, 2);
            $table->decimal('weight_applied', 3, 1);
            $table->timestamps();
            
            $table->foreign('report_id')->references('id')->on('essentials_performance_reviews')->onDelete('cascade');
            $table->foreign('kpi_id')->references('id')->on('performance_kpis')->onDelete('cascade');
            $table->index(['report_id', 'kpi_id']);
        });

        // Create performance aggregations table
        Schema::create('performance_aggregations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('department_id')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('overall_score', 4, 2);
            $table->json('kpi_breakdown');
            $table->integer('ranking_position')->nullable();
            $table->decimal('percentile', 5, 2)->nullable();
            $table->timestamps();
            
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('categories')->onDelete('set null');
            $table->index(['employee_id', 'period_start', 'period_end'], 'perf_agg_emp_period_idx');
            $table->index(['department_id', 'period_start', 'period_end'], 'perf_agg_dept_period_idx');
        });

        // Create performance goals table
        Schema::create('performance_goals', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('employee_id');
            $table->unsignedBigInteger('kpi_id');
            $table->decimal('target_score', 4, 2);
            $table->decimal('current_score', 4, 2)->default(0);
            $table->decimal('achievement_percentage', 5, 2)->default(0);
            $table->date('period_start');
            $table->date('period_end');
            $table->enum('status', ['active', 'achieved', 'missed', 'cancelled'])->default('active');
            $table->timestamps();
            
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('kpi_id')->references('id')->on('performance_kpis')->onDelete('cascade');
            $table->index(['employee_id', 'status'], 'perf_goals_emp_status_idx');
            $table->index(['kpi_id', 'status'], 'perf_goals_kpi_status_idx');
        });

        // Create performance alerts table
        Schema::create('performance_alerts', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['threshold_breach', 'goal_missed', 'review_due', 'submission_overdue']);
            $table->unsignedInteger('employee_id');
            $table->unsignedBigInteger('kpi_id')->nullable();
            $table->enum('threshold_type', ['excellent', 'good', 'needs_improvement'])->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('triggered_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('kpi_id')->references('id')->on('performance_kpis')->onDelete('cascade');
            $table->index(['employee_id', 'is_read'], 'perf_alerts_emp_read_idx');
            $table->index(['type', 'is_read'], 'perf_alerts_type_read_idx');
        });

        // Add new columns to existing performance templates table
        Schema::table('essentials_performance_templates', function (Blueprint $table) {
            $table->json('kpi_config')->nullable()->after('scoring_criteria');
            $table->enum('status', ['draft', 'active', 'inactive'])->default('draft')->after('is_active');
        });

        // Add new columns to existing performance reviews table
        Schema::table('essentials_performance_reviews', function (Blueprint $table) {
            $table->json('form_data')->nullable()->after('responses');
            $table->json('calculated_kpis')->nullable()->after('form_data');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('performance_alerts');
        Schema::dropIfExists('performance_goals');
        Schema::dropIfExists('performance_aggregations');
        Schema::dropIfExists('kpi_scores');
        Schema::dropIfExists('kpi_thresholds');
        Schema::dropIfExists('template_kpi_mapping');
        Schema::dropIfExists('performance_kpis');
        
        Schema::table('essentials_performance_templates', function (Blueprint $table) {
            $table->dropColumn(['kpi_config', 'status']);
        });
        
        Schema::table('essentials_performance_reviews', function (Blueprint $table) {
            $table->dropColumn(['form_data', 'calculated_kpis']);
        });
    }
};
