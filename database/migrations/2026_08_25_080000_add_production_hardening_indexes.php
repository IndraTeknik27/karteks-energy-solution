<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Composite indexes for production hardening.
 *
 * Addresses audit findings from FASE 6 (Production Hardening Pass):
 * - DashboardService stats queries (latest orders by status) force filesort
 * - CatalogController default sort (latest published_at filtered by status) forces filesort
 * - User dashboard orders pagination (latest created_at per customer) forces filesort
 * - Order status history rendering in Blade (sorted in PHP) needs indexed order_id + created_at
 *
 * MySQL 8+ supports descending index but Laravel schema doesn't expose that yet.
 * Composite (a, b) handles WHERE a=? ORDER BY b without filesort.
 *
 * Use raw SQL `ALTER TABLE ... ADD INDEX` (not schema builder) so we don't
 * re-run existing unique/FK indexes if migration runs twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        // orders: [status, created_at] for DashboardService stats (status filter + latest sort)
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'idx_orders_status_created_at');
        });

        // orders: [customer_id, created_at] for DashboardOrderController::index pagination
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['customer_id', 'created_at'], 'idx_orders_customer_created_at');
        });

        // products: [status, published_at] for CatalogController::index default sort
        Schema::table('products', function (Blueprint $table) {
            $table->index(['status', 'published_at'], 'idx_products_status_published_at');
        });

        // order_status_histories: [order_id, created_at] for order detail page history
        Schema::table('order_status_histories', function (Blueprint $table) {
            $table->index(['order_id', 'created_at'], 'idx_osh_order_created_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_status_created_at');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_customer_created_at');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_status_published_at');
        });
        Schema::table('order_status_histories', function (Blueprint $table) {
            $table->dropIndex('idx_osh_order_created_at');
        });
    }
};
