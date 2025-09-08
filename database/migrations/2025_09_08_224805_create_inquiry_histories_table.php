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
        Schema::create('inquiry_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->constrained('inquiries', 'inquiry_id')->onDelete('cascade')->comment('問い合わせID');
            $table->foreignId('user_id')->constrained('users')->comment('操作者ID');
            $table->string('action', 50)->comment('アクション');
            $table->string('field_name', 100)->nullable()->comment('変更されたフィールド名');
            $table->text('old_value')->nullable()->comment('変更前の値');
            $table->text('new_value')->nullable()->comment('変更後の値');
            $table->text('comment')->nullable()->comment('コメント');
            $table->timestamp('created_at')->comment('変更日時');

            $table->index(['inquiry_id', 'created_at']);
            $table->index(['user_id', 'action']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiry_histories');
    }
};
