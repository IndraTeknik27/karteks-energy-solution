<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Api\Controller;
use App\Models\Address;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $items = $user->wishlists()
            ->with('product:id,name,slug,price,sale_price,status,stock_qty,manage_stock')
            ->latest('created_at')
            ->get()
            ->pluck('product')
            ->filter();

        return $this->success(
            $items->values(),
            'Wishlist pelanggan.',
        );
    }

    public function toggle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $user = $request->user();
        $existing = Wishlist::where('customer_id', $user->id)
            ->where('product_id', $data['product_id'])
            ->first();

        if ($existing) {
            $existing->delete();
            return $this->success(null, 'Produk dihapus dari wishlist.');
        }

        Wishlist::create([
            'customer_id' => $user->id,
            'product_id' => $data['product_id'],
        ]);

        return $this->success(null, 'Produk ditambahkan ke wishlist.', 201);
    }

    public function clear(Request $request): JsonResponse
    {
        $request->user()->wishlists()->delete();
        return $this->success(null, 'Wishlist dikosongkan.');
    }

    public function moveToCart(Request $request, int $product): JsonResponse
    {
        // Implemented via CartService move-to-cart - handled in API cart or web flow
        return $this->success(null, 'Pindah ke keranjang via CartService.');
    }
}