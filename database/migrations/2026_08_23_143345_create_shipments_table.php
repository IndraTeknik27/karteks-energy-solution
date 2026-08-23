<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number')->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->string('courier_code')->nullable()->comment('jne, jnt, sicepat, dll');
            $table->string('courier_name')->nullable();
            $table->string('courier_service')->nullable()->comment('REG, YES, OKE');
            $table->string('tracking_number')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('cost', 15, 2)->default(0);
            $table->enum('status', ['pending', 'packed', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'returned', 'failed'])->default('pending');
            $table->json('origin_address')->nullable();
            $table->json('destination_address')->nullable();
            $table->timestamp('packed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index('tracking_number');
        });

        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->integer('qty');
            $table->timestamps();

            $table->index('shipment_id');
        });

        Schema::create('shipment_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->string('status');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['shipment_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_trackings');
        Schema::dropIfExists('shipment_items');
        Schema::dropIfExists('shipments');
    }
};