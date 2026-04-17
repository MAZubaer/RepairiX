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
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'phone')) {
            return;
        }

        // Existing DBs may still have a unique index from old migrations.
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_phone_unique');
            });
        } catch (\Throwable $e) {
            // Ignore if index does not exist.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'phone')) {
            return;
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('phone');
            });
        } catch (\Throwable $e) {
            // Ignore if index already exists.
        }
    }
};
