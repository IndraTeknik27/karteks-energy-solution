<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('variation_id')->nullable()->constrained('product_variations')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->integer('qty')->default(0)->comment('total stock');
            $table->integer('reserved_qty')->default(0)->comment('reserved untuk order aktif');
            $table->integer('available_qty')->storedAs('qty - reserved_qty');
            $table->integer('low_stock_threshold')->default(5);
            $table->string('location_code')->nullable()->comment('rak/bin location');
            $table->timestamps();

            $table->unique(['product_id', 'variation_id', 'warehouse_id'], 'inventories_unique');
            $table->index('qty');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};