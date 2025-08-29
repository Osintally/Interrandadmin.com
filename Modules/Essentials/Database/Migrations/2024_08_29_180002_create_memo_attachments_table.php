<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemoAttachmentsTable extends Migration
{
    public function up()
    {
        Schema::create('essentials_memo_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('memo_id');
            $table->string('filename');
            $table->string('storage_path');
            $table->string('mime_type');
            $table->bigInteger('size');
            $table->integer('version')->default(1);
            $table->timestamps();
            
            $table->foreign('memo_id')->references('id')->on('essentials_memos')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('essentials_memo_attachments');
    }
}
