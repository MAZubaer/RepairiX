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
        Schema::create('shops', function (Blueprint $table) {
            $table->id('shop_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('shop_name');
            $table->enum('subscription_status', ['not_activated', 'activated'])->default('not_activated');
            $table->dateTime('expiry_date')->nullable();
            $table->text('description')->nullable();
            $table->decimal('rating', 3, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
