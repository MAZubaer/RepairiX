<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('service_records')) {
            return;
        }

        DB::statement("ALTER TABLE service_records MODIFY COLUMN status ENUM('pending','accepted','rejected','in progress','completed','sent from shop','delivered') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('service_records')) {
            return;
        }

        DB::statement("ALTER TABLE service_records MODIFY COLUMN status ENUM('accepted','rejected','in progress','completed','sent from shop','delivered') NOT NULL");
    }
};
