<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Services\V1\CartService;
use App\Services\V1\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected CartService $cartService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $cart = $this->cartService->getOrCreateCart($user, $request->session()->get('cart_session_id'));

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong.');
        }

        $addresses = $user->addresses()->orderByDesc('is_primary')->orderByDesc('updated_at')->get();
        $defaultAddress = $addresses->where('is_primary', true)->first() ?? $addresses->first();

        $shippingOptions = $defaultAddress
            ? $this->cartService->calculateShipping($cart, $defaultAddress->city, 'jne')
            : [];

        return view('pages.checkout.index', compact('cart', 'addresses', 'defaultAddress', 'shippingOptions'));
    }

    public function preview(Request $request)
    {
        $user = $request->user();
        $cart = $this->cartService->getOrCreateCart($user, $request->session()->get('cart_session_id'));

        $shippingAddressId = $request->input('shipping_address_id');
        $courier = $request->input('shipping_courier', 'jne');

        $shippingAddress = $user->addresses()->where('id', $shippingAddressId)->first();
        $preview = $this->orderService->preview($user, $cart, $shippingAddress, $courier);

        return response()->json(['success' => true, 'data' => $preview]);
    }

    public function place(Request $request)
    {
        $data = $request->validate([
            'shipping_address_id' => ['required', 'integer'],
            'shipping_courier' => ['required', 'string', 'in:jne,pos,tiki,sicepat,jnt'],
            'shipping_service' => ['required', 'string', 'in:REG,YES,OKE'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'in:midtrans,bank_transfer,manual'],
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_email' => ['required', 'email'],
            'customer_phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[1-9][0-9]{6,10}$/'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
            'billing_same_as_shipping' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $cart = $this->cartService->getOrCreateCart($user, $request->session()->get('cart_session_id'));

        try {
            $order = $this->orderService->placeOrder($user, $data, $cart);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['checkout' => $e->getMessage()]);
        }

        // Clear guest session after successful order
        $request->session()->forget('cart_session_id');

        return redirect()->route('dashboard.orders.show', $order->order_number)
            ->with('success', "Order {$order->order_number} berhasil dibuat. Silakan lakukan pembayaran.");
    }
}