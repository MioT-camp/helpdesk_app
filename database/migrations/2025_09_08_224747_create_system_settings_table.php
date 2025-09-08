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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique()->comment('設定キー');
            $table->text('value')->nullable()->comment('設定値');
            $table->string('type', 20)->comment('データ型');
            $table->string('description', 255)->nullable()->comment('説明');
            $table->boolean('is_public')->default(false)->comment('公開設定');
            $table->timestamps();

            $table->index('key');
            $table->index(['is_public', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
