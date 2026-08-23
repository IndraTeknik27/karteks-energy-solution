<?php

namespace App\Http\Controllers\Api\V1\Checkout;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Api\V1\Checkout\PlaceOrderRequest;
use App\Http\Requests\Api\V1\Checkout\PreviewCheckoutRequest;
use App\Http\Resources\V1\OrderResource;
use App\Models\Address;
use App\Services\V1\CartService;
use App\Services\V1\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected CartService $cartService,
    ) {}

    public function preview(PreviewCheckoutRequest $request): JsonResponse
    {
        $user = $request->user();
        $context = $this->resolveCartContext($request);

        $cart = $this->cartService->getOrCreateCart($context['user'], $context['session_id']);

        if ($cart->items->isEmpty()) {
            return $this->error('Cart kosong. Tambahkan item terlebih dahulu.', 422);
        }

        $shippingAddress = null;
        if ($request->filled('shipping_address_id')) {
            $shippingAddress = Address::where('customer_id', $user->id)
                ->where('id', $request->shipping_address_id)
                ->first();
        } elseif ($primary = $user->addresses()->where('is_primary', true)->first()) {
            $shippingAddress = $primary;
        }

        $preview = $this->orderService->preview(
            $user,
            $cart,
            $shippingAddress,
            $request->input('shipping_courier'),
            $request->input('coupon_code'),
        );

        $response = $this->success(
            $preview,
            'Preview order berhasil dimuat.',
        );

        if ($context['generated']) {
            $response->headers->set('X-Session-Id', $context['session_id']);
        }

        return $response;
    }

    public function placeOrder(PlaceOrderRequest $request): JsonResponse
    {
        $user = $request->user();
        $context = $this->resolveCartContext($request);
        $cart = $this->cartService->getOrCreateCart($context['user'], $context['session_id']);
        $data = $request->validated();

        try {
            $order = $this->orderService->placeOrder($user, $data, $cart);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            new OrderResource($order),
            "Order {$order->order_number} berhasil dibuat. Silakan lakukan pembayaran.",
            201,
        );
    }

    public function validateStock(Request $request): JsonResponse
    {
        $context = $this->resolveCartContext($request);
        $cart = $this->cartService->getOrCreateCart($context['user'], $context['session_id']);

        if ($cart->items->isEmpty()) {
            return $this->error('Cart kosong.', 422);
        }

        $issues = [];
        foreach ($cart->items as $item) {
            $itemable = $item->itemable;
            if (! $itemable) {
                $issues[] = [
                    'cart_item_id' => $item->id,
                    'itemable_id' => $item->itemable_id,
                    'message' => 'Produk sudah tidak tersedia.',
                ];
                continue;
            }

            $available = $itemable instanceof \App\Models\ProductVariation
                ? max(0, (int) $itemable->stock_qty - (int) $itemable->reserved_qty)
                : (int) $itemable->stock_qty;

            if ($item->qty > $available) {
                $issues[] = [
                    'cart_item_id' => $item->id,
                    'name' => $itemable->name,
                    'requested' => $item->qty,
                    'available' => $available,
                    'message' => "Stok tidak cukup untuk {$itemable->name}. Tersedia: {$available}.",
                ];
            }
        }

        return $this->success([
            'all_stock_ok' => empty($issues),
            'issues' => $issues,
            'item_count' => $cart->items->sum('qty'),
        ], empty($issues) ? 'Semua stok tersedia.' : 'Beberapa stok tidak mencukupi.');
    }

    protected function resolveCartContext(Request $request): array
    {
        $user = $request->user();
        $sessionId = $request->header('X-Session-Id');
        $generated = false;

        if (! $user && (! $sessionId || ! \Illuminate\Support\Str::isUuid($sessionId))) {
            $sessionId = (string) \Illuminate\Support\Str::uuid();
            $generated = true;
        }

        return [
            'user' => $user,
            'session_id' => $sessionId,
            'generated' => $generated,
        ];
    }
}