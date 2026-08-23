<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Services\V1\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService) {}

    public function index(Request $request)
    {
        $cart = $this->resolveCart($request);
        $this->loadCartRelations($cart);

        return view('pages.cart.index', compact('cart'));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'itemable_type' => ['required', 'in:product,variation'],
            'itemable_id' => ['required', 'integer'],
            'qty' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $cart = $this->resolveCart($request);

        try {
            $this->cartService->addItem(
                $cart,
                $data['itemable_type'],
                (int) $data['itemable_id'],
                (int) $data['qty'],
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['cart' => $e->getMessage()]);
        }

        return redirect()->route('cart.index')->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request, int $item)
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        $cart = $this->resolveCart($request);
        $cartItem = $cart->items()->find($item);

        if (! $cartItem) {
            abort(404, 'Item tidak ditemukan.');
        }

        try {
            $this->cartService->updateItemQty($cartItem, (int) $data['qty']);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['cart' => $e->getMessage()]);
        }

        return redirect()->route('cart.index')->with('success', 'Keranjang diperbarui.');
    }

    public function remove(Request $request, int $item)
    {
        $cart = $this->resolveCart($request);
        $cartItem = $cart->items()->find($item);

        if (! $cartItem) {
            abort(404, 'Item tidak ditemukan.');
        }

        $this->cartService->removeItem($cartItem);

        return redirect()->route('cart.index')->with('success', 'Item dihapus dari keranjang.');
    }

    public function clear(Request $request)
    {
        $cart = $this->resolveCart($request);
        $this->cartService->clear($cart);

        return redirect()->route('cart.index')->with('success', 'Keranjang dikosongkan.');
    }

    public function applyCoupon(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'min:3', 'max:50'],
        ]);

        $cart = $this->resolveCart($request);

        try {
            $this->cartService->applyCoupon($cart, $data['code']);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['coupon' => $e->getMessage()]);
        }

        return redirect()->route('cart.index')->with('success', 'Coupon diterapkan.');
    }

    public function removeCoupon(Request $request)
    {
        $cart = $this->resolveCart($request);
        $this->cartService->removeCoupon($cart);

        return redirect()->route('cart.index')->with('success', 'Coupon dihapus.');
    }

    protected function resolveCart(Request $request): Cart
    {
        $user = $request->user();

        if ($user) {
            // Authenticated: use customer_id; also merge guest cart if session had one
            $this->mergeGuestCartIfAny($request);

            return $this->cartService->getOrCreateCart($user, null);
        }

        // Guest: store session_id in web session
        $sessionId = $request->session()->get('cart_session_id');
        if (! $sessionId) {
            $sessionId = (string) Str::uuid();
            $request->session()->put('cart_session_id', $sessionId);
        }

        return $this->cartService->getOrCreateCart(null, $sessionId);
    }

    protected function mergeGuestCartIfAny(Request $request): void
    {
        $sessionId = $request->session()->get('cart_session_id');
        if ($sessionId && $request->user()) {
            $this->cartService->mergeGuestCartOnLogin($request->user(), $sessionId);
            $request->session()->forget('cart_session_id');
        }
    }

    protected function loadCartRelations(Cart $cart): void
    {
        $cart->load(['items.itemable']);
    }
}