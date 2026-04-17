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
        Schema::create('service_records', function (Blueprint $table) {
            $table->id('service_id');

            $table->foreignId('customer_id')
                ->constrained('customers', 'customer_id')
                ->cascadeOnDelete();

            $table->foreignId('shop_id')
                ->constrained('shops', 'shop_id')
                ->cascadeOnDelete();

            $table->string('phone_number', 30);
            $table->string('phone_model');
            $table->string('phone_imei_number', 50);
            $table->text('customer_problem');
            $table->text('shop_problem')->nullable();

            $table->enum('status', [
                'pending',
                'accepted',
                'rejected',
                'in progress',
                'completed',
                'sent from shop',
                'delivered',
            ]);

            $table->decimal('repair_cost', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_records');
    }
};
