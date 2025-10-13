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
        Schema::create('payroll_approval_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_record_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('action', ['submitted', 'reviewed', 'approved', 'rejected']);
            $table->text('comments')->nullable();
            $table->timestamps();
            
            // Note: Foreign key constraints will be added later if needed
            
            // Indexes for better performance
            $table->index(['payroll_record_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_approval_history');
    }
};
