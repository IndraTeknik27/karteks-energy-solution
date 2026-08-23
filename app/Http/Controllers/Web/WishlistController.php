<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use App\Services\V1\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    public function __construct(protected CartService $cartService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $items = $user->wishlists()
            ->with(['product' => fn ($q) => $q->with(['category', 'brand', 'images'])])
            ->latest('created_at')
            ->get()
            ->pluck('product')
            ->filter();

        return view('dashboard.wishlist.index', ['items' => $items]);
    }

    public function toggle(Request $request, Product $product)
    {
        $user = $request->user();

        $existing = Wishlist::where('customer_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();
            return back()->with('success', 'Produk dihapus dari wishlist.');
        }

        Wishlist::create([
            'customer_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return back()->with('success', 'Produk ditambahkan ke wishlist.');
    }

    public function remove(Request $request, Product $product)
    {
        $user = $request->user();
        Wishlist::where('customer_id', $user->id)
            ->where('product_id', $product->id)
            ->delete();

        return back()->with('success', 'Produk dihapus dari wishlist.');
    }

    public function clear(Request $request)
    {
        $user = $request->user();
        Wishlist::where('customer_id', $user->id)->delete();

        return redirect()->route('dashboard.wishlist')->with('success', 'Wishlist dikosongkan.');
    }

    public function moveToCart(Request $request, Product $product)
    {
        $user = $request->user();

        if (! $product || $product->status !== 'published') {
            return back()->withErrors(['wishlist' => 'Produk tidak tersedia.']);
        }

        $available = (int) $product->stock_qty;
        if ($product->manage_stock && $available < 1) {
            return back()->withErrors(['wishlist' => 'Stok produk habis.']);
        }

        $context = ['user' => $user, 'session_id' => $request->session()->get('cart_session_id')];
        $cart = $this->cartService->getOrCreateCart($user, $context['session_id']);

        try {
            $this->cartService->addItem($cart, 'product', $product->id, 1);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['wishlist' => $e->getMessage()]);
        }

        Wishlist::where('customer_id', $user->id)
            ->where('product_id', $product->id)
            ->delete();

        return redirect()->route('cart.index')->with('success', "{$product->name} dipindahkan ke keranjang.");
    }
}