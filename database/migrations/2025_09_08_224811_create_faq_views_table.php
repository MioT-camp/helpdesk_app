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
        Schema::create('faq_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_id')->constrained('faqs', 'faq_id')->onDelete('cascade')->comment('FAQ ID');
            $table->foreignId('user_id')->nullable()->constrained('users')->comment('閲覧者ID');
            $table->string('ip_address', 45)->nullable()->comment('IPアドレス');
            $table->text('user_agent')->nullable()->comment('ユーザーエージェント');
            $table->timestamp('viewed_at')->comment('閲覧日時');

            $table->index(['faq_id', 'viewed_at']);
            $table->index(['user_id', 'viewed_at']);
            $table->index('ip_address');
            $table->index('viewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq_views');
    }
};
