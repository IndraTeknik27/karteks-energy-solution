<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\V1\MidtransService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(protected MidtransService $midtransService) {}

    public function show(Request $request, string $orderNumber)
    {
        $order = $request->user()->orders()
            ->where('order_number', $orderNumber)
            ->with(['items.itemable', 'payments'])
            ->first();

        if (! $order) {
            abort(404, 'Order tidak ditemukan.');
        }

        if (! in_array($order->status, ['pending_payment', 'payment_pending'], true)) {
            return redirect()
                ->route('dashboard.orders.show', $order->order_number)
                ->with('info', "Order dengan status '{$order->status}' tidak memerlukan pembayaran.");
        }

        if (! $this->midtransService->isConfigured()) {
            return view('pages.payment.error', [
                'order' => $order,
                'reason' => 'Pembayaran online belum tersedia saat ini. Silakan hubungi customer service untuk pembayaran manual.',
            ]);
        }

        $latestPayment = $order->payments()->latest('created_at')->first();
        $snapToken = null;
        $clientKey = $this->midtransService->clientKey();
        $error = null;

        if ($latestPayment && $latestPayment->snap_token && $latestPayment->is_pending && $latestPayment->expired_at?->isFuture()) {
            $snapToken = $latestPayment->snap_token;
        } else {
            try {
                $payment = $this->midtransService->createSnapToken($order, $request->user()->id);
                $snapToken = $payment->snap_token;
            } catch (InvalidArgumentException $e) {
                $error = $e->getMessage();
            } catch (Throwable $e) {
                $error = 'Gagal memuat Snap token: '.$e->getMessage();
            }
        }

        return view('pages.payment.show', [
            'order' => $order,
            'snapToken' => $snapToken,
            'clientKey' => $clientKey,
            'snapUrl' => config('midtrans.snap_url'),
            'error' => $error,
        ]);
    }

    public function finish(Request $request, string $orderNumber)
    {
        $order = $request->user()->orders()->where('order_number', $orderNumber)->first();
        if (! $order) {
            abort(404, 'Order tidak ditemukan.');
        }

        return view('pages.payment.finish', ['order' => $order]);
    }

    public function unfinish(Request $request, string $orderNumber)
    {
        $order = $request->user()->orders()->where('order_number', $orderNumber)->first();
        if (! $order) {
            abort(404, 'Order tidak ditemukan.');
        }

        return view('pages.payment.unfinish', ['order' => $order]);
    }

    public function error(Request $request, string $orderNumber)
    {
        $order = $request->user()->orders()->where('order_number', $orderNumber)->first();
        if (! $order) {
            abort(404, 'Order tidak ditemukan.');
        }

        return view('pages.payment.error', [
            'order' => $order,
            'reason' => 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.',
        ]);
    }
}