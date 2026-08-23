<?php

namespace App\Http\Controllers\Api\V1\Payment;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\V1\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\V1\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PaymentController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService,
    ) {}

    public function status(Request $request, string $orderNumber): JsonResponse
    {
        $user = $request->user();

        $order = Order::where('order_number', $orderNumber)
            ->where('customer_id', $user->id)
            ->first();

        if (! $order) {
            return $this->notFound('Order tidak ditemukan.');
        }

        $latestPayment = $order->payments()
            ->latest('created_at')
            ->first();

        if (! $latestPayment) {
            return $this->notFound('Belum ada payment untuk order ini.');
        }

        return $this->success(
            (new PaymentResource($latestPayment->load('order')))->toArray($request),
            'Status payment berhasil dimuat.',
        );
    }

    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        $payments = Payment::whereHas('order', function ($q) use ($user) {
            $q->where('customer_id', $user->id);
        })
            ->with('order:id,order_number,total,status')
            ->latest('created_at')
            ->paginate(min((int) $request->input('per_page', 15), 100));

        return $this->success(
            $payments,
            'Riwayat payment berhasil dimuat.',
        );
    }

    public function refresh(Request $request, string $orderNumber): JsonResponse
    {
        $user = $request->user();

        $order = Order::where('order_number', $orderNumber)
            ->where('customer_id', $user->id)
            ->first();

        if (! $order) {
            return $this->notFound('Order tidak ditemukan.');
        }

        $payment = $order->payments()
            ->whereNotNull('transaction_id')
            ->latest('created_at')
            ->first();

        if (! $payment) {
            return $this->notFound('Payment belum memiliki transaction_id.');
        }

        try {
            $updated = $this->midtransService->refreshStatusFromGateway($payment);

            return $this->success(
                (new PaymentResource($updated->load('order')))->toArray($request),
                'Status payment berhasil di-refresh dari Midtrans.',
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->error(
                'Gagal refresh status dari Midtrans: '.$e->getMessage(),
                500
            );
        }
    }
}