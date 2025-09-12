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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->json('fields'); // Template structure
            $table->json('scoring_criteria')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('created_by')->references('id')->on('employees');
            
            $table->index(['department_id', 'is_active']);
            $table->index(['created_by']);
        });

        // Template fields table for better structure
        Schema::create('template_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id');
            $table->string('field_type'); // text, rating, select, file, number, date
            $table->string('label');
            $table->json('options')->nullable(); // For select fields
            $table->boolean('is_required')->default(false);
            $table->integer('order_index')->default(0);
            $table->decimal('weight', 4, 2)->default(1.00); // Scoring weight
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');
            $table->index(['template_id', 'order_index']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('template_fields');
        Schema::dropIfExists('templates');
    }
};