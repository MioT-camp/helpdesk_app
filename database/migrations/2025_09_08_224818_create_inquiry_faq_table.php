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
        Schema::create('inquiry_faq', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->constrained('inquiries', 'inquiry_id')->onDelete('cascade')->comment('問い合わせID');
            $table->foreignId('faq_id')->constrained('faqs', 'faq_id')->onDelete('cascade')->comment('FAQ ID');
            $table->tinyInteger('relevance')->nullable()->comment('関連度（1-5）');
            $table->foreignId('linked_by')->constrained('users')->comment('紐付け実行者ID');
            $table->timestamp('created_at')->comment('紐付け日時');

            $table->unique(['inquiry_id', 'faq_id']);
            $table->index(['inquiry_id', 'relevance']);
            $table->index(['faq_id', 'relevance']);
            $table->index('linked_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiry_faq');
    }
};
