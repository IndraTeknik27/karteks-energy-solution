<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->enum('pricing_type', ['fixed', 'starting_price', 'quotation', 'free'])->default('fixed');
            $table->decimal('base_price', 15, 2)->nullable();
            $table->decimal('starting_price', 15, 2)->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('image')->nullable();
            $table->json('features')->nullable();
            $table->json('requirements')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort')->default(0);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'is_featured']);
            $table->index('sort');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};