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
        Schema::create('faq_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_id')->constrained('faqs', 'faq_id')->onDelete('cascade')->comment('FAQ ID');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade')->comment('タグID');

            $table->unique(['faq_id', 'tag_id']);
            $table->index('faq_id');
            $table->index('tag_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq_tag');
    }
};
