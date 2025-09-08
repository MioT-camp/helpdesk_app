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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('attachable_type', 255)->comment('関連モデル');
            $table->unsignedBigInteger('attachable_id')->comment('関連モデルID');
            $table->string('filename', 255)->comment('ファイル名');
            $table->string('original_name', 255)->comment('元のファイル名');
            $table->string('mime_type', 100)->comment('MIMEタイプ');
            $table->unsignedBigInteger('size')->comment('ファイルサイズ（バイト）');
            $table->string('path', 500)->comment('ファイルパス');
            $table->foreignId('uploaded_by')->constrained('users')->comment('アップロード者ID');
            $table->timestamps();

            $table->index(['attachable_type', 'attachable_id']);
            $table->index('mime_type');
            $table->index('uploaded_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
