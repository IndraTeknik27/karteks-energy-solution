<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->morphs('itemable');
            $table->integer('qty')->default(1);
            $table->decimal('price_snapshot', 15, 2)->comment('harga saat dimasukkan ke cart');
            $table->decimal('subtotal', 15, 2)->storedAs('qty * price_snapshot');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['cart_id', 'itemable_type', 'itemable_id'], 'cart_items_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};