<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // `image_desktop` & `image_mobile` sekarang legacy (banner pakai Spatie Media).
        // Make nullable supaya banner text-only / CTA-only bisa dibuat tanpa upload gambar.
        Schema::table('banners', function (Blueprint $table) {
            $table->string('image_desktop')->nullable()->change();
            $table->string('image_mobile')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->string('image_desktop')->nullable(false)->change();
            $table->string('image_mobile')->nullable()->change();
        });
    }
};