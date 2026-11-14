<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `disk` records where each image lives ("public" = local storage, "cloudinary" = CDN),
     * so existing local images keep working after switching new uploads to Cloudinary.
     * For Cloudinary images, `image_path` holds the Cloudinary public_id.
     */
    public function up(): void
    {
        Schema::table('shop_images', function (Blueprint $table) {
            if (! Schema::hasColumn('shop_images', 'disk')) {
                $table->string('disk', 20)->default('public')->after('image_path');
            }

            if (! Schema::hasColumn('shop_images', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shop_images', function (Blueprint $table) {
            $table->dropColumn(['disk', 'sort_order']);
        });
    }
};
