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
        if (! $this->isMysqlWithTable()) {
            return;
        }

        DB::statement("ALTER TABLE service_records MODIFY COLUMN status ENUM('pending','accepted','rejected','in progress','completed','sent from shop','delivered') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->isMysqlWithTable()) {
            return;
        }

        DB::statement("ALTER TABLE service_records MODIFY COLUMN status ENUM('accepted','rejected','in progress','completed','sent from shop','delivered') NOT NULL");
    }

    /**
     * MODIFY COLUMN is MySQL/MariaDB syntax. On other drivers (e.g. SQLite in tests) the
     * create_service_records_table migration already includes 'pending', so nothing to do.
     */
    private function isMysqlWithTable(): bool
    {
        return Schema::hasTable('service_records')
            && in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }
};
