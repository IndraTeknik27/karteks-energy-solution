<?php

namespace App\Http\Controllers\Api\V1\Payment;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Api\V1\Payment\InitiatePaymentRequest;
use App\Http\Resources\V1\PaymentResource;
use App\Models\Order;
use App\Services\V1\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class MidtransController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService,
    ) {}

    public function initiate(InitiatePaymentRequest $request, string $orderNumber): JsonResponse
    {
        $user = $request->user();

        $order = Order::where('order_number', $orderNumber)
            ->where('customer_id', $user->id)
            ->with(['items.itemable', 'payments'])
            ->first();

        if (! $order) {
            return $this->notFound('Order tidak ditemukan atau bukan milik Anda.');
        }

        if (! in_array($order->status, ['pending_payment', 'payment_pending'], true)) {
            return $this->error(
                "Order dengan status '{$order->status}' tidak dapat dibayar. Hubungi customer service.",
                422
            );
        }

        if (! $this->midtransService->isConfigured()) {
            return $this->error(
                'Payment gateway belum dikonfigurasi. Hubungi admin untuk setup Midtrans.',
                503
            );
        }

        try {
            $payment = $this->midtransService->createSnapToken($order, $user->id);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (Throwable $e) {
            Log::channel('midtrans')->error('snap_token_creation_failed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);

            return $this->error(
                'Gagal membuat Snap token: '.$e->getMessage(),
                500
            );
        }

        return $this->success(
            (new PaymentResource($payment->load('order')))->toArray($request),
            "Snap token berhasil dibuat untuk order {$order->order_number}.",
            201,
        );
    }

    public function notification(Request $request): JsonResponse
    {
        $payload = $request->all();

        if (empty($payload) || empty($payload['order_id'])) {
            return $this->error('Payload notification kosong atau tidak valid.', 400);
        }

        try {
            $payment = $this->midtransService->handleNotification($payload);
        } catch (InvalidArgumentException $e) {
            Log::channel('midtrans')->warning('notification_validation_failed', [
                'payload_order_id' => $payload['order_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->error($e->getMessage(), 400);
        } catch (Throwable $e) {
            Log::channel('midtrans')->error('notification_handling_failed', [
                'payload_order_id' => $payload['order_id'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error(
                'Gagal memproses notification: '.$e->getMessage(),
                500
            );
        }

        return $this->success(
            [
                'payment_number' => $payment->payment_number,
                'order_number' => $payment->order?->order_number,
                'status' => $payment->status,
            ],
            'Notification berhasil diproses.',
        );
    }
}