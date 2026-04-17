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
        Schema::table('shops', function (Blueprint $table) {
            if (! Schema::hasColumn('shops', 'motto')) {
                $table->string('motto')->nullable()->after('description');
            }

            if (! Schema::hasColumn('shops', 'services_provided')) {
                $table->longText('services_provided')->nullable()->after('motto');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            if (Schema::hasColumn('shops', 'services_provided')) {
                $table->dropColumn('services_provided');
            }

            if (Schema::hasColumn('shops', 'motto')) {
                $table->dropColumn('motto');
            }
        });
    }
};
