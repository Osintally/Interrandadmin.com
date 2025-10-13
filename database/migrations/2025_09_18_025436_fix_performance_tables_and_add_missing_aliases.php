<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create view for essentials_performances that maps to essentials_performance_reviews
        DB::statement('CREATE VIEW essentials_performances AS SELECT 
            id,
            business_id,
            user_id as employee_id,
            reviewer_id,
            template_id,
            review_period,
            review_date,
            overall_score,
            scores,
            responses,
            strengths,
            areas_for_improvement,
            goals,
            development_plan,
            comments,
            status,
            submitted_at,
            approved_at,
            approved_by,
            created_at,
            updated_at
        FROM essentials_performance_reviews');

        // Create view for taxonomies that maps to categories  
        DB::statement('CREATE VIEW taxonomies AS SELECT 
            id,
            name,
            business_id,
            short_code,
            parent_id,
            created_by,
            category_type as type,
            description,
            created_at,
            updated_at
        FROM categories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS essentials_performances');
        DB::statement('DROP VIEW IF EXISTS taxonomies');
    }
};
