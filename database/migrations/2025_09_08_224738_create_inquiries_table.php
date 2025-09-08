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
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id('inquiry_id')->comment('問い合わせID');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'closed'])
                ->default('pending')->comment('回答状況');
            $table->timestamp('received_at')->comment('受信日時');
            $table->string('sender_email', 255)->comment('送信元メールアドレス');
            $table->string('customer_id', 100)->nullable()->comment('顧客ID');
            $table->string('prefecture', 20)->nullable()->comment('都道府県');
            $table->string('user_attribute', 50)->nullable()->comment('ユーザー属性');
            $table->foreignId('category_id')->nullable()->constrained('categories')->comment('カテゴリID');
            $table->string('subject', 500)->comment('メールの件名');
            $table->text('summary')->nullable()->comment('要約');
            $table->longText('content')->comment('メール本文');
            $table->longText('response')->nullable()->comment('回答');
            $table->json('linked_faq_ids')->nullable()->comment('紐付きFAQ IDの配列');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->comment('担当者ID');
            $table->foreignId('created_user_id')->constrained('users')->comment('登録者ID');
            $table->tinyInteger('priority')->default(2)->comment('優先度（1:低、2:中、3:高、4:緊急）');
            $table->timestamp('response_deadline')->nullable()->comment('回答期限');
            $table->timestamp('first_response_at')->nullable()->comment('初回回答日時');
            $table->timestamp('completed_at')->nullable()->comment('完了日時');
            $table->text('search_keywords')->nullable()->comment('検索用統合テキスト');
            $table->json('attachments')->nullable()->comment('添付ファイル情報');
            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index(['assigned_user_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index(['response_deadline', 'status']);
            $table->index('sender_email');
            $table->index('received_at');
            $table->fullText(['subject', 'content', 'summary', 'search_keywords']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
