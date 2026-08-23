<?php

namespace App\Http\Controllers\Api\V1\Cart;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Api\V1\Cart\AddCartItemRequest;
use App\Http\Requests\Api\V1\Cart\ApplyCouponRequest;
use App\Http\Requests\Api\V1\Cart\CalculateShippingRequest;
use App\Http\Requests\Api\V1\Cart\UpdateCartItemRequest;
use App\Http\Resources\V1\CartItemResource;
use App\Http\Resources\V1\CartResource;
use App\Models\CartItem;
use App\Services\V1\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService) {}

    public function index(Request $request): JsonResponse
    {
        $context = $this->resolveCartContext($request);
        $cart = $this->cartService->getOrCreateCart($context['user'], $context['session_id']);

        $response = $this->success(
            new CartResource($cart),
            'Cart berhasil dimuat.',
        );

        if ($context['generated']) {
            $response->headers->set('X-Session-Id', $context['session_id']);
        }

        return $response;
    }

    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        $context = $this->resolveCartContext($request);
        $cart = $this->cartService->getOrCreateCart($context['user'], $context['session_id']);
        $data = $request->validated();

        try {
            $item = $this->cartService->addItem(
                $cart,
                $data['itemable_type'],
                (int) $data['itemable_id'],
                (int) $data['qty'],
                $data['notes'] ?? null,
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        $cart = $cart->fresh(['items.itemable']);

        $response = $this->success([
            'cart' => new CartResource($cart),
            'added_item' => new CartItemResource($item),
        ], 'Item berhasil ditambahkan ke cart.');

        if ($context['generated']) {
            $response->headers->set('X-Session-Id', $context['session_id']);
        }

        return $response;
    }

    public function updateItem(UpdateCartItemRequest $request, string $item): JsonResponse
    {
        $context = $this->resolveCartContext($request);
        $cartItem = $this->resolveCartItem($context, (int) $item);
        $data = $request->validated();

        try {
            $updatedItem = $this->cartService->updateItemQty($cartItem, (int) $data['qty']);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        $cart = $cartItem->cart->fresh(['items.itemable']);

        $responseData = ['cart' => new CartResource($cart)];

        if ($updatedItem->exists) {
            $responseData['item'] = new CartItemResource($updatedItem);
        }

        $message = (int) $data['qty'] === 0
            ? 'Item dihapus dari cart.'
            : 'Jumlah item diperbarui.';

        return $this->success($responseData, $message);
    }

    public function removeItem(Request $request, string $item): JsonResponse
    {
        $context = $this->resolveCartContext($request);
        $cartItem = $this->resolveCartItem($context, (int) $item);

        $cart = $this->cartService->removeItem($cartItem);

        return $this->success(
            new CartResource($cart),
            'Item berhasil dihapus dari cart.',
        );
    }

    public function clear(Request $request): JsonResponse
    {
        $context = $this->resolveCartContext($request);
        $cart = $this->cartService->getOrCreateCart($context['user'], $context['session_id']);

        $cart = $this->cartService->clear($cart);

        return $this->success(
            new CartResource($cart),
            'Cart berhasil dikosongkan.',
        );
    }

    public function applyCoupon(ApplyCouponRequest $request): JsonResponse
    {
        $context = $this->resolveCartContext($request);
        $cart = $this->cartService->getOrCreateCart($context['user'], $context['session_id']);
        $data = $request->validated();

        try {
            $cart = $this->cartService->applyCoupon($cart, $data['code']);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            new CartResource($cart),
            'Coupon berhasil diterapkan.',
        );
    }

    public function removeCoupon(Request $request): JsonResponse
    {
        $context = $this->resolveCartContext($request);
        $cart = $this->cartService->getOrCreateCart($context['user'], $context['session_id']);

        $cart = $this->cartService->removeCoupon($cart);

        return $this->success(
            new CartResource($cart),
            'Coupon berhasil dihapus.',
        );
    }

    public function calculateShipping(CalculateShippingRequest $request): JsonResponse
    {
        $context = $this->resolveCartContext($request);
        $cart = $this->cartService->getOrCreateCart($context['user'], $context['session_id']);
        $data = $request->validated();

        $options = $this->cartService->calculateShipping(
            $cart,
            $data['city'],
            $data['courier'] ?? 'jne',
        );

        return $this->success([
            'cart_subtotal' => (float) $cart->subtotal,
            'cart_subtotal_formatted' => 'Rp '.number_format((float) $cart->subtotal, 0, ',', '.'),
            'destination_city' => $data['city'],
            'options' => $options,
        ], 'Opsi pengiriman berhasil dihitung.');
    }

    protected function resolveCartContext(Request $request): array
    {
        $user = $request->user();
        $sessionId = $request->header('X-Session-Id') ?? $request->cookie('cart_session_id');
        $generated = false;

        if (! $user) {
            if (! $sessionId || ! Str::isUuid($sessionId)) {
                $sessionId = (string) Str::uuid();
                $generated = true;
            }
        }

        return [
            'user' => $user,
            'session_id' => $sessionId,
            'generated' => $generated,
        ];
    }

    protected function resolveCartItem(array $context, int $itemId): CartItem
    {
        $cartItem = CartItem::with('cart', 'itemable')->find($itemId);

        if (! $cartItem) {
            abort(404, 'Item cart tidak ditemukan.');
        }

        $cart = $cartItem->cart;
        $user = $context['user'];

        if ($user) {
            if ($cart->customer_id !== $user->id) {
                abort(403, 'Item ini bukan milik Anda.');
            }
        } else {
            if ($cart->session_id !== $context['session_id']) {
                abort(403, 'Item ini bukan milik sesi Anda.');
            }
        }

        return $cartItem;
    }
}