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
        Schema::create('essentials_user_performance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('template_id');
            $table->string('review_period');
            $table->string('review_cycle')->default('Quarterly');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('pending');
            $table->json('performance_data');
            $table->decimal('overall_rating', 3, 1)->nullable();
            $table->unsignedBigInteger('reviewer_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->index(['business_id', 'user_id']);
            $table->index(['template_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('essentials_user_performance');
    }
};
