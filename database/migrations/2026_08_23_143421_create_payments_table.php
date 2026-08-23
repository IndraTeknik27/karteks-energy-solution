<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('payment_number')->unique();
            $table->enum('gateway', ['midtrans', 'manual_transfer', 'xendit', 'other'])->default('midtrans');
            $table->string('transaction_id')->nullable()->comment('from gateway');
            $table->string('payment_type')->nullable()->comment('bank_transfer, ewallet, qris, dll');
            $table->string('va_number')->nullable()->comment('virtual account');
            $table->string('bank')->nullable();
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('fee_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->nullable();
            $table->enum('status', [
                'pending', 'authorized', 'captured', 'settlement',
                'denied', 'cancelled', 'expired', 'failed', 'refunded', 'partial_refunded',
            ])->default('pending');
            $table->string('fraud_status')->nullable();
            $table->text('signature_key')->nullable();
            $table->json('raw_request')->nullable();
            $table->json('raw_response')->nullable();
            $table->text('redirect_url')->nullable();
            $table->text('snap_token')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index('transaction_id');
            $table->index('status');
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('type', ['bank_transfer', 'ewallet', 'qris', 'credit_card', 'virtual_account', 'cod', 'manual'])->default('bank_transfer');
            $table->string('logo')->nullable();
            $table->decimal('fee_percent', 5, 2)->default(0);
            $table->decimal('fee_fixed', 15, 2)->default(0);
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort')->default(0);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('payments');
    }
};