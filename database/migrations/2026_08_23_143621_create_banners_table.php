<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('image_desktop');
            $table->string('image_mobile')->nullable();
            $table->string('link_url')->nullable();
            $table->string('link_text')->nullable()->comment('CTA button text');
            $table->string('link_target')->default('_self');
            $table->enum('position', ['home_hero', 'home_secondary', 'category_top', 'sidebar', 'popup'])->default('home_hero');
            $table->integer('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->integer('click_count')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'position']);
            $table->index('sort');
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->unique()->comment('header, footer, sidebar, mobile');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->string('title');
            $table->string('url')->nullable();
            $table->string('route_name')->nullable();
            $table->json('route_params')->nullable();
            $table->string('icon')->nullable();
            $table->string('target')->default('_self');
            $table->integer('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['menu_id', 'parent_id', 'sort']);
        });

        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('hero, featured_products, ev_categories, dll');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->enum('type', [
                'hero_banner', 'featured_products', 'categories', 'banners',
                'latest_blogs', 'testimonials', 'brands', 'custom_html',
            ])->default('featured_products');
            $table->json('settings')->nullable();
            $table->integer('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_sections');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('banners');
    }
};