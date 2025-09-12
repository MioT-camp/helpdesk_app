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
        Schema::table('inquiries', function (Blueprint $table) {
            // ENUMに'no_response'を追加
            $table->enum('status', ['pending', 'in_progress', 'completed', 'closed', 'no_response'])
                ->default('pending')
                ->comment('回答状況')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            // ENUMから'no_response'を削除
            $table->enum('status', ['pending', 'in_progress', 'completed', 'closed'])
                ->default('pending')
                ->comment('回答状況')
                ->change();
        });
    }
};
