<?php

namespace App\Services\V1;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Midtrans\Config as MidtransConfig;
use Midtrans\Notification as MidtransNotification;
use Midtrans\Snap;
use Midtrans\Transaction;

/**
 * MidtransService
 *
 * Wrapper untuk Midtrans Snap + Core API.
 * Menangani: createSnapToken, handleNotification, refund, cancel, status check.
 *
 * Semua response Midtrans disimpan mentah di Payment.raw_request/raw_response
 * untuk audit trail. Credential TIDAK pernah di-expose ke client.
 */
class MidtransService
{
    protected bool $initialized = false;

    public function __construct()
    {
        $this->initialize();
    }

    protected function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        MidtransConfig::$serverKey = config('midtrans.server_key');
        MidtransConfig::$clientKey = config('midtrans.client_key');
        MidtransConfig::$isProduction = config('midtrans.environment') === 'production';
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = (bool) (config('midtrans.secure.enabled') ?? false);

        if ($override = config('midtrans.notification_url')) {
            MidtransConfig::$overrideNotifUrl = $override;
        }

        $this->initialized = true;
    }

    public function isConfigured(): bool
    {
        return filled(config('midtrans.server_key')) && filled(config('midtrans.client_key'));
    }

    public function clientKey(): ?string
    {
        return $this->isConfigured() ? config('midtrans.client_key') : null;
    }

    /**
     * Generate Snap token untuk order.
     *
     * Flow:
     * - Validasi order milik customer
     * - Cek order belum punya payment sukses
     * - Build Snap params dari order + items
     * - Call Snap::createTransaction()
     * - Simpan Payment record dengan snap_token + raw response
     */
    public function createSnapToken(Order $order, int $customerId): Payment
    {
        if (! $this->isConfigured()) {
            throw new InvalidArgumentException(
                'Midtrans belum dikonfigurasi. Isi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY di .env.'
            );
        }

        if ((int) $order->customer_id !== $customerId) {
            throw new InvalidArgumentException('Order ini bukan milik Anda.');
        }

        if ($order->is_paid) {
            throw new InvalidArgumentException(
                "Order {$order->order_number} sudah lunas. Tidak dapat membuat pembayaran baru."
            );
        }

        $existingSuccessful = $order->payments()
            ->whereIn('status', ['settlement', 'captured'])
            ->exists();

        if ($existingSuccessful) {
            throw new InvalidArgumentException(
                "Order {$order->order_number} sudah memiliki pembayaran sukses."
            );
        }

        $params = $this->buildSnapParams($order);

        $snapResponse = DB::transaction(function () use ($order, $params) {
            $snapResponse = Snap::createTransaction($params);

            $responseArray = json_decode(json_encode($snapResponse), true);

            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_number' => $this->generatePaymentNumber(),
                'gateway' => 'midtrans',
                'transaction_id' => $responseArray['transaction_id'] ?? null,
                'payment_type' => null,
                'gross_amount' => (float) $order->total,
                'fee_amount' => 0,
                'net_amount' => null,
                'status' => 'pending',
                'raw_request' => $params,
                'raw_response' => $responseArray,
                'snap_token' => $responseArray['token'] ?? null,
                'redirect_url' => $responseArray['redirect_url'] ?? null,
                'expired_at' => $this->calculateExpiry(),
            ]);

            $this->logInfo('snap_token_created', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'transaction_id' => $payment->transaction_id,
            ]);

            return $payment;
        });

        return $snapResponse;
    }

    /**
     * Handle notification (webhook) dari Midtrans.
     *
     * Steps:
     * 1. Validate signature (sha512 of order_id+status_code+gross_amount+server_key)
     * 2. Check idempotency - skip if already processed
     * 3. Map transaction_status ke status internal
     * 4. Update Payment record
     * 5. Trigger Order status transition if settlement/captured
     */
    public function handleNotification(array $payload): Payment
    {
        if (! $this->isConfigured()) {
            throw new InvalidArgumentException('Midtrans belum dikonfigurasi.');
        }

        $this->validateSignature($payload);

        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        if (! $orderId) {
            throw new InvalidArgumentException('Payload notification tidak valid: order_id kosong.');
        }

        $payment = Payment::where('transaction_id', $orderId)
            ->orWhere('payment_number', $orderId)
            ->orWhereHas('order', fn ($q) => $q->where('order_number', $orderId))
            ->latest()
            ->first();

        if (! $payment) {
            throw new InvalidArgumentException(
                "Payment untuk transaction_id={$orderId} tidak ditemukan."
            );
        }

        if ($this->isAlreadyProcessed($payment, $payload)) {
            $this->logInfo('notification_duplicate_skipped', [
                'payment_id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'transaction_id' => $orderId,
                'transaction_status' => $transactionStatus,
            ]);

            return $payment;
        }

        return DB::transaction(function () use ($payment, $payload, $transactionStatus, $fraudStatus) {
            $previousStatus = $payment->status;

            $internalStatus = $this->mapTransactionStatus($transactionStatus, $fraudStatus);

            $payment->update([
                'transaction_id' => $payload['transaction_id'] ?? $payment->transaction_id,
                'payment_type' => $payload['payment_type'] ?? $payment->payment_type,
                'va_number' => $this->extractVaNumber($payload),
                'bank' => $payload['bank'] ?? $payment->bank,
                'gross_amount' => $payload['gross_amount'] ?? $payment->gross_amount,
                'status' => $internalStatus,
                'fraud_status' => $fraudStatus ?? $payment->fraud_status,
                'signature_key' => $payload['signature_key'] ?? null,
                'raw_response' => array_merge((array) $payment->raw_response, $payload),
                'paid_at' => $this->shouldMarkPaid($internalStatus) ? now() : $payment->paid_at,
            ]);

            $this->logInfo('notification_processed', [
                'payment_id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'order_number' => $payment->order?->order_number,
                'previous_status' => $previousStatus,
                'new_status' => $internalStatus,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
            ]);

            if ($this->shouldMarkPaid($internalStatus)) {
                $this->markOrderAsPaid($payment);
            } elseif (in_array($internalStatus, ['cancelled', 'expired', 'failed', 'denied'], true)) {
                $this->markOrderAsCancelled($payment, $internalStatus);
            }

            return $payment->fresh(['order']);
        });
    }

    public function refreshStatusFromGateway(Payment $payment): Payment
    {
        if (! $payment->transaction_id) {
            throw new InvalidArgumentException('Payment belum memiliki transaction_id.');
        }

        $status = Transaction::status($payment->transaction_id);
        $statusArray = json_decode(json_encode($status), true);

        $internalStatus = $this->mapTransactionStatus(
            $statusArray['transaction_status'] ?? null,
            $statusArray['fraud_status'] ?? null
        );

        $previousStatus = $payment->status;

        $payment->update([
            'transaction_id' => $statusArray['transaction_id'] ?? $payment->transaction_id,
            'payment_type' => $statusArray['payment_type'] ?? $payment->payment_type,
            'va_number' => $this->extractVaNumber($statusArray),
            'bank' => $statusArray['bank'] ?? $payment->bank,
            'gross_amount' => $statusArray['gross_amount'] ?? $payment->gross_amount,
            'status' => $internalStatus,
            'fraud_status' => $statusArray['fraud_status'] ?? $payment->fraud_status,
            'raw_response' => array_merge((array) $payment->raw_response, ['refresh' => $statusArray]),
            'paid_at' => $this->shouldMarkPaid($internalStatus) && ! $payment->paid_at ? now() : $payment->paid_at,
        ]);

        if ($previousStatus !== $internalStatus) {
            if ($this->shouldMarkPaid($internalStatus)) {
                $this->markOrderAsPaid($payment);
            } elseif (in_array($internalStatus, ['cancelled', 'expired', 'failed', 'denied'], true)) {
                $this->markOrderAsCancelled($payment, $internalStatus);
            }
        }

        return $payment->fresh(['order']);
    }

    public function refund(Payment $payment, float $amount, ?string $reason = null): array
    {
        if (! $this->isConfigured()) {
            throw new InvalidArgumentException('Midtrans belum dikonfigurasi.');
        }

        if (! $payment->is_successful) {
            throw new InvalidArgumentException(
                "Payment {$payment->payment_number} belum sukses. Tidak bisa refund."
            );
        }

        $params = [
            'refund_key' => 'refund-'.$payment->id.'-'.time(),
            'amount' => $amount,
            'reason' => $reason ?? 'Refund diminta oleh admin.',
        ];

        $response = Transaction::refund($payment->transaction_id, $params);
        $responseArray = json_decode(json_encode($response), true);

        $payment->update([
            'status' => (float) $amount >= (float) $payment->gross_amount ? 'refunded' : 'partial_refunded',
            'raw_response' => array_merge((array) $payment->raw_response, ['refund' => $responseArray]),
        ]);

        if ($payment->order) {
            $payment->order->update(['status' => OrderStatusHistory::STATUS_REFUNDED]);
            OrderStatusHistory::create([
                'order_id' => $payment->order->id,
                'from_status' => $payment->order->getOriginal('status'),
                'to_status' => OrderStatusHistory::STATUS_REFUNDED,
                'note' => 'Refund sebesar Rp '.number_format($amount, 0, ',', '.').' via Midtrans.',
                'changed_by_role' => 'admin',
            ]);
        }

        $this->logInfo('refund_processed', [
            'payment_id' => $payment->id,
            'payment_number' => $payment->payment_number,
            'amount' => $amount,
            'reason' => $reason,
        ]);

        return $responseArray;
    }

    public function cancelPendingTransaction(Payment $payment): Payment
    {
        if (! $payment->transaction_id) {
            throw new InvalidArgumentException('Payment belum memiliki transaction_id.');
        }

        try {
            Transaction::cancel($payment->transaction_id);
        } catch (\Throwable $e) {
            $this->logWarning('midtrans_cancel_failed', [
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'error' => $e->getMessage(),
            ]);
        }

        $payment->update(['status' => 'cancelled']);

        if ($payment->order && $payment->order->status === OrderStatusHistory::STATUS_PENDING_PAYMENT) {
            $this->markOrderAsCancelled($payment, 'cancelled');
        }

        return $payment->fresh(['order']);
    }

    /**
     * Verify signature: sha512(order_id + status_code + gross_amount + server_key).
     */
    public function validateSignature(array $payload): bool
    {
        if (! config('midtrans.validate_signature', true)) {
            return true;
        }

        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $serverKey = config('midtrans.server_key');

        $expected = openssl_digest(
            $orderId.$statusCode.$grossAmount.$serverKey,
            'sha512'
        );

        $provided = $payload['signature_key'] ?? '';

        if (! hash_equals($expected, (string) $provided)) {
            $this->logWarning('signature_validation_failed', [
                'order_id' => $orderId,
                'expected_prefix' => substr($expected, 0, 16),
                'provided_prefix' => substr((string) $provided, 0, 16),
            ]);

            throw new InvalidArgumentException('Signature key tidak valid.');
        }

        return true;
    }

    protected function buildSnapParams(Order $order): array
    {
        $items = $order->items->map(function ($item) {
            return [
                'id' => (string) $item->itemable_id,
                'price' => (int) round((float) $item->price),
                'quantity' => (int) $item->qty,
                'name' => mb_substr((string) $item->name, 0, 50),
                'category' => $item->itemable_type === \App\Models\ProductVariation::class
                    ? 'Product Variation'
                    : 'Product',
            ];
        })->toArray();

        if ((float) $order->shipping_cost > 0) {
            $items[] = [
                'id' => 'shipping',
                'price' => (int) round((float) $order->shipping_cost),
                'quantity' => 1,
                'name' => 'Ongkos Kirim ('.($order->shipping_courier ?? '-').')',
                'category' => 'Shipping',
            ];
        }

        if ((float) $order->coupon_discount > 0) {
            $items[] = [
                'id' => 'discount',
                'price' => -(int) round((float) $order->coupon_discount),
                'quantity' => 1,
                'name' => 'Diskon '.($order->coupon_code ?? ''),
                'category' => 'Discount',
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) round((float) $order->total),
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => mb_substr((string) $order->customer_name, 0, 50),
                'email' => (string) $order->customer_email,
                'phone' => (string) $order->customer_phone,
                'shipping_address' => $this->extractShippingAddress($order),
            ],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'hours',
                'duration' => max(1, (int) config('midtrans.expiry.snap_token', 24)),
            ],
        ];

        $enabledPayments = config('midtrans.enabled_payments');
        if (is_array($enabledPayments) && ! empty($enabledPayments)) {
            $params['enabled_payments'] = $enabledPayments;
        }

        $finishUrl = config('midtrans.finish_url');
        $unfinishUrl = config('midtrans.unfinish_url');
        $errorUrl = config('midtrans.error_url');

        if ($finishUrl || $unfinishUrl || $errorUrl) {
            $params['callbacks'] = array_filter([
                'finish' => $finishUrl,
                'unfinish' => $unfinishUrl,
                'error' => $errorUrl,
            ]);
        }

        return $params;
    }

    protected function extractShippingAddress(Order $order): array
    {
        $addr = (array) $order->shipping_address;

        return [
            'first_name' => (string) ($addr['recipient'] ?? $order->customer_name),
            'phone' => (string) ($addr['phone'] ?? $order->customer_phone),
            'address' => (string) ($addr['address_line_1'] ?? ''),
            'city' => (string) ($addr['city'] ?? ''),
            'postal_code' => (string) ($addr['postal_code'] ?? ''),
            'country_code' => 'IDN',
        ];
    }

    protected function extractVaNumber(array $payload): ?string
    {
        foreach (['va_number', 'permata_va_number', 'bill_key'] as $key) {
            if (! empty($payload[$key])) {
                return (string) $payload[$key];
            }
        }

        if (isset($payload['va_numbers']) && is_array($payload['va_numbers'])) {
            $first = $payload['va_numbers'][0] ?? null;
            if ($first && ! empty($first['va_number'])) {
                return (string) $first['va_number'];
            }
        }

        return null;
    }

    protected function mapTransactionStatus(?string $transactionStatus, ?string $fraudStatus): string
    {
        if ($transactionStatus === 'capture') {
            return $fraudStatus === 'challenge' ? 'pending' : 'captured';
        }

        return match ($transactionStatus) {
            'settlement' => 'settlement',
            'pending' => 'pending',
            'deny', 'denied' => 'denied',
            'cancel', 'cancelled' => 'cancelled',
            'expire', 'expired' => 'expired',
            'failure', 'failed' => 'failed',
            'refund' => 'refunded',
            'partial_refund' => 'partial_refunded',
            'authorize' => 'authorized',
            default => 'pending',
        };
    }

    protected function shouldMarkPaid(string $internalStatus): bool
    {
        return in_array($internalStatus, ['settlement', 'captured'], true);
    }

    protected function isAlreadyProcessed(Payment $payment, array $payload): bool
    {
        if (! in_array($payment->status, ['pending'], true)) {
            return true;
        }

        $windowHours = (int) config('midtrans.idempotency_window_hours', 24);
        if ($payment->updated_at && $payment->updated_at->lt(now()->subHours($windowHours))) {
            return false;
        }

        $incomingSignature = $payload['signature_key'] ?? null;
        if ($incomingSignature && $payment->signature_key === $incomingSignature) {
            return true;
        }

        if (isset($payload['transaction_id']) && $payment->transaction_id === $payload['transaction_id']) {
            $incomingStatus = $this->mapTransactionStatus(
                $payload['transaction_status'] ?? null,
                $payload['fraud_status'] ?? null
            );
            if ($incomingStatus === $payment->status) {
                return true;
            }
        }

        return false;
    }

    protected function markOrderAsPaid(Payment $payment): void
    {
        $order = $payment->order;
        if (! $order) {
            return;
        }

        if ($order->is_paid) {
            return;
        }

        $previousStatus = $order->status;

        $order->update([
            'status' => OrderStatusHistory::STATUS_PAID,
            'paid_at' => $payment->paid_at ?? now(),
        ]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $previousStatus,
            'to_status' => OrderStatusHistory::STATUS_PAID,
            'note' => "Pembayaran diterima via Midtrans ({$payment->payment_number}).",
            'changed_by_role' => 'system',
        ]);
    }

    protected function markOrderAsCancelled(Payment $payment, string $paymentStatus): void
    {
        $order = $payment->order;
        if (! $order) {
            return;
        }

        if (! in_array($order->status, [
            OrderStatusHistory::STATUS_PENDING_PAYMENT,
            OrderStatusHistory::STATUS_PAYMENT_PENDING,
        ], true)) {
            return;
        }

        $orderStatus = match ($paymentStatus) {
            'expired' => OrderStatusHistory::STATUS_EXPIRED,
            'denied', 'failed' => OrderStatusHistory::STATUS_FAILED,
            default => OrderStatusHistory::STATUS_CANCELLED,
        };

        $previousStatus = $order->status;
        $order->update([
            'status' => $orderStatus,
            'cancelled_at' => now(),
        ]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $previousStatus,
            'to_status' => $orderStatus,
            'note' => "Order otomatis di-{$orderStatus} karena payment {$paymentStatus}.",
            'changed_by_role' => 'system',
        ]);
    }

    protected function calculateExpiry(): Carbon
    {
        $minutes = (int) config('midtrans.expiry.snap_token', 60);

        return now()->addMinutes($minutes);
    }

    public function generatePaymentNumber(): string
    {
        $prefix = config('karteks.numbering.payment.prefix', 'PAY');
        $padding = (int) config('karteks.numbering.payment.padding', 5);
        $today = now()->format('Ymd');

        $lastPayment = Payment::where('payment_number', 'like', "{$prefix}-{$today}-%")
            ->orderByDesc('id')
            ->first();

        if ($lastPayment) {
            $parts = explode('-', $lastPayment->payment_number);
            $sequence = (int) end($parts);
            $sequence++;
        } else {
            $sequence = 1;
        }

        return sprintf('%s-%s-%0'.$padding.'d', $prefix, $today, $sequence);
    }

    protected function logInfo(string $event, array $context = []): void
    {
        if (! config('midtrans.logging.enabled', true)) {
            return;
        }

        Log::channel(config('midtrans.logging.channel', 'midtrans'))
            ->info("[midtrans] {$event}", $context);
    }

    protected function logWarning(string $event, array $context = []): void
    {
        Log::channel(config('midtrans.logging.channel', 'midtrans'))
            ->warning("[midtrans] {$event}", $context);
    }
}
