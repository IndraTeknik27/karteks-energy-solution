<?php

namespace App\Http\Controllers\Api\V1\Order;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Api\V1\Order\CancelOrderRequest;
use App\Http\Resources\V1\OrderResource;
use App\Models\Order;
use App\Services\V1\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Order::where('customer_id', $user->id)
            ->with(['items', 'statusHistories']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $allowedSorts = ['latest', 'oldest', 'total_high', 'total_low'];
        $sort = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'latest';

        match ($sort) {
            'oldest' => $query->orderBy('created_at'),
            'total_high' => $query->orderByDesc('total'),
            'total_low' => $query->orderBy('total'),
            default => $query->latest('created_at'),
        };

        $perPage = min((int) $request->input('per_page', 10), 50);
        $orders = $query->paginate($perPage);

        return $this->success(
            OrderResource::collection($orders),
            'Daftar order.',
        );
    }

    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $user = $request->user();
        $order = Order::where('order_number', $orderNumber)
            ->where('customer_id', $user->id)
            ->with(['items', 'statusHistories'])
            ->first();

        if (! $order) {
            return $this->notFound('Order tidak ditemukan.');
        }

        return $this->success(
            new OrderResource($order),
            'Detail order.',
        );
    }

    public function cancel(CancelOrderRequest $request, string $orderNumber): JsonResponse
    {
        $user = $request->user();
        $order = Order::where('order_number', $orderNumber)
            ->where('customer_id', $user->id)
            ->first();

        if (! $order) {
            return $this->notFound('Order tidak ditemukan.');
        }

        try {
            $order = $this->orderService->cancelOrder($order, $request->validated('reason'), $user);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            new OrderResource($order->load(['items', 'statusHistories'])),
            "Order {$order->order_number} berhasil dibatalkan. Stok telah dikembalikan.",
        );
    }

    public function confirmDelivery(Request $request, string $orderNumber): JsonResponse
    {
        $user = $request->user();
        $order = Order::where('order_number', $orderNumber)
            ->where('customer_id', $user->id)
            ->first();

        if (! $order) {
            return $this->notFound('Order tidak ditemukan.');
        }

        try {
            $order = $this->orderService->confirmDelivery($order, $user);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            new OrderResource($order->load(['items', 'statusHistories'])),
            "Order {$order->order_number} dikonfirmasi. Terima kasih!",
        );
    }

    public function tracking(Request $request, string $orderNumber): JsonResponse
    {
        $user = $request->user();
        $order = Order::where('order_number', $orderNumber)
            ->where('customer_id', $user->id)
            ->with(['statusHistories' => fn ($q) => $q->orderBy('created_at')])
            ->first();

        if (! $order) {
            return $this->notFound('Order tidak ditemukan.');
        }

        $trackingSteps = [
            ['status' => 'PENDING_PAYMENT', 'label' => 'Menunggu Pembayaran', 'done' => true],
            ['status' => 'PAID', 'label' => 'Pembayaran Diterima', 'done' => $order->is_paid],
            ['status' => 'PROCESSING', 'label' => 'Sedang Diproses', 'done' => in_array($order->status, ['processing', 'ready_to_ship', 'shipped', 'delivered', 'completed'])],
            ['status' => 'READY_TO_SHIP', 'label' => 'Siap Dikirim', 'done' => in_array($order->status, ['ready_to_ship', 'shipped', 'delivered', 'completed'])],
            ['status' => 'SHIPPED', 'label' => 'Sedang Dikirim', 'done' => in_array($order->status, ['shipped', 'delivered', 'completed'])],
            ['status' => 'DELIVERED', 'label' => 'Terkirim', 'done' => in_array($order->status, ['delivered', 'completed'])],
            ['status' => 'COMPLETED', 'label' => 'Selesai', 'done' => $order->status === 'completed'],
        ];

        return $this->success([
            'order_number' => $order->order_number,
            'status' => $order->status,
            'shipping_courier' => $order->shipping_courier,
            'shipping_service' => $order->shipping_service,
            'shipping_tracking_number' => $order->shipping_tracking_number,
            'tracking_steps' => $trackingSteps,
            'status_history' => $order->statusHistories->map(fn ($h) => [
                'to_status' => $h->to_status,
                'note' => $h->note,
                'created_at' => $h->created_at?->toIso8601String(),
            ])->values(),
        ], 'Tracking order.');
    }

    public function invoice(Request $request, string $orderNumber): JsonResponse
    {
        $user = $request->user();
        $order = Order::where('order_number', $orderNumber)
            ->where('customer_id', $user->id)
            ->with(['items'])
            ->first();

        if (! $order) {
            return $this->notFound('Order tidak ditemukan.');
        }

        return $this->success([
            'invoice_number' => 'INV-'.substr($order->order_number, 4),
            'order_number' => $order->order_number,
            'invoice_date' => $order->created_at?->toDateString(),
            'company' => [
                'name' => config('karteks.company.name'),
                'address' => config('karteks.company.address'),
                'phone' => config('karteks.company.phone'),
                'email' => config('karteks.company.email'),
                'website' => config('karteks.company.website'),
            ],
            'customer' => [
                'name' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => $order->customer_phone,
            ],
            'shipping_address' => $order->shipping_address,
            'billing_address' => $order->billing_address,
            'items' => $order->items->map(fn ($item) => [
                'name' => $item->name,
                'sku' => $item->sku,
                'qty' => $item->qty,
                'price' => (float) $item->price,
                'price_formatted' => 'Rp '.number_format((float) $item->price, 0, ',', '.'),
                'subtotal' => (float) $item->subtotal,
                'subtotal_formatted' => 'Rp '.number_format((float) $item->subtotal, 0, ',', '.'),
            ])->values(),
            'subtotal' => (float) $order->subtotal,
            'subtotal_formatted' => 'Rp '.number_format((float) $order->subtotal, 0, ',', '.'),
            'discount' => (float) $order->coupon_discount,
            'discount_formatted' => 'Rp '.number_format((float) $order->coupon_discount, 0, ',', '.'),
            'coupon_code' => $order->coupon_code,
            'tax' => (float) $order->tax,
            'tax_formatted' => 'Rp '.number_format((float) $order->tax, 0, ',', '.'),
            'shipping_cost' => (float) $order->shipping_cost,
            'shipping_cost_formatted' => 'Rp '.number_format((float) $order->shipping_cost, 0, ',', '.'),
            'total' => (float) $order->total,
            'total_formatted' => 'Rp '.number_format((float) $order->total, 0, ',', '.'),
        ], 'Invoice order.');
    }
}