<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemoRecipientsTable extends Migration
{
    public function up()
    {
        Schema::create('essentials_memo_recipients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('memo_id')->unsigned();
            $table->integer('user_id');
            $table->enum('recipient_type', ['to', 'cc', 'bcc']);
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->foreign('memo_id')->references('id')->on('essentials_memos')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('essentials_memo_recipients');
    }
}
