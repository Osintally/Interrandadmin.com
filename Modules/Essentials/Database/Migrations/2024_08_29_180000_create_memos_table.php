<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemosTable extends Migration
{
    public function up()
    {
        Schema::create('essentials_memos', function (Blueprint $table) {
            $table->bigIncrements('id');
<<<<<<< HEAD
            $table->integer('business_id')->unsigned();
            $table->string('subject');
            $table->longText('body');
            $table->integer('sender_id')->unsigned();
=======
            $table->integer('business_id');
            $table->string('subject');
            $table->longText('body');
            $table->integer('sender_id');
>>>>>>> 8bb22bf (Implement corporate memos system)
            $table->enum('status', ['draft', 'sent', 'archived'])->default('draft');
            $table->timestamps();
            
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('essentials_memos');
    }
}
