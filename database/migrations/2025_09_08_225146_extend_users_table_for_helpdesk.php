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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'manager', 'staff'])->default('staff')->after('password');
            $table->string('department', 100)->nullable()->after('role');
            $table->json('specialties')->nullable()->after('department');
            $table->boolean('is_active')->default(true)->after('specialties');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'department',
                'specialties',
                'is_active',
                'last_login_at',
            ]);
        });
    }
};
