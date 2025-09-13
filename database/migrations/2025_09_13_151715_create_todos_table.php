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
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // ToDoの内容
            $table->text('description')->nullable(); // 詳細説明
            $table->date('due_date')->nullable(); // 期限
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium'); // 優先度
            $table->boolean('is_completed')->default(false); // 完了フラグ
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 作成者
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todos');
    }
};
