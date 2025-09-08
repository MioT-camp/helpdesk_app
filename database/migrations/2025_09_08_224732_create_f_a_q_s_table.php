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
        Schema::create('faqs', function (Blueprint $table) {
            $table->id('faq_id')->comment('FAQ ID');
            $table->foreignId('category_id')->constrained('categories')->comment('カテゴリID');
            $table->text('question')->comment('質問文');
            $table->text('answer')->comment('回答文');
            $table->foreignId('user_id')->constrained('users')->comment('登録者ID');
            $table->unsignedInteger('count')->default(0)->comment('閲覧回数');
            $table->boolean('is_active')->default(true)->comment('公開フラグ');
            $table->string('tags', 255)->nullable()->comment('検索用タグ');
            $table->text('search_keywords')->nullable()->comment('検索用統合テキスト');
            $table->tinyInteger('priority')->default(1)->comment('優先度（1:低、2:中、3:高）');
            $table->tinyInteger('difficulty')->default(1)->comment('難易度（1:簡単、2:普通、3:難しい）');
            $table->timestamps();

            $table->index(['is_active', 'category_id']);
            $table->index(['priority', 'difficulty']);
            $table->index('count');
            $table->index('user_id');
            $table->fullText(['question', 'answer', 'tags', 'search_keywords']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
