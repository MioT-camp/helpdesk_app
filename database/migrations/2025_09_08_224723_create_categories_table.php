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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('カテゴリ名');
            $table->string('slug', 100)->unique()->comment('URL用スラッグ');
            $table->string('color', 7)->nullable()->comment('表示色（HEX）');
            $table->boolean('is_active')->default(true)->comment('有効フラグ');
            $table->timestamps();

            $table->index(['is_active', 'name']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
