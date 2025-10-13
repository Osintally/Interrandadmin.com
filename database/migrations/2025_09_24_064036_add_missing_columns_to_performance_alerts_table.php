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
        Schema::table('performance_alerts', function (Blueprint $table) {
            $table->string('title')->nullable()->after('type');
            $table->unsignedBigInteger('goal_id')->nullable()->after('kpi_id');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->after('message');
            $table->enum('status', ['active', 'resolved', 'cancelled'])->default('active')->after('priority');
            $table->unsignedInteger('resolved_by')->nullable()->after('resolved_at');
            $table->text('resolution_notes')->nullable()->after('resolved_by');
            
            $table->foreign('goal_id')->references('id')->on('performance_goals')->onDelete('cascade');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('performance_alerts', function (Blueprint $table) {
            $table->dropForeign(['goal_id']);
            $table->dropForeign(['resolved_by']);
            $table->dropColumn(['title', 'goal_id', 'priority', 'status', 'resolved_by', 'resolution_notes']);
        });
    }
};
