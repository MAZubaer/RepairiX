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
        if (Schema::hasColumn('shops', 'shop_name')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->dropColumn('shop_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('shops', 'shop_name')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->string('shop_name')->nullable()->after('user_id');
            });
        }
    }
};
