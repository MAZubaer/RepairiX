<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * RepairiX covers all digital devices, not only phones:
 * - shops list the device types they repair (existing shops get all of them);
 * - each request records its device type (existing requests were phones);
 * - the IMEI field becomes an optional serial/IMEI, since laptops and accessories have none.
 */
return new class extends Migration
{
    private const ALL = ['phone', 'laptop', 'tablet', 'accessory'];

    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->json('device_types')->nullable()->after('services_provided');
        });

        DB::table('shops')->update(['device_types' => json_encode(self::ALL)]);

        Schema::table('service_records', function (Blueprint $table) {
            $table->string('device_type', 20)->default('phone')->after('shop_id');
            $table->string('phone_imei_number', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('service_records')->whereNull('phone_imei_number')->update(['phone_imei_number' => '']);

        Schema::table('service_records', function (Blueprint $table) {
            $table->string('phone_imei_number', 50)->nullable(false)->change();
            $table->dropColumn('device_type');
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('device_types');
        });
    }
};
