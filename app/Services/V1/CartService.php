<?php

namespace App\Services\V1;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CartService
{
    public function getOrCreateCart(?User $user, ?string $sessionId = null): Cart
    {
        $cartTtlDays = (int) config('karteks.inventory.cart_ttl_days', 30);
        $expiresAt = now()->addDays($cartTtlDays);

        if ($user) {
            $cart = Cart::active()
                ->forCustomer($user->id)
                ->orderByDesc('updated_at')
                ->first();

            if (! $cart) {
                $cart = Cart::create([
                    'customer_id' => $user->id,
                    'session_id' => null,
                    'expires_at' => $expiresAt,
                ]);
            }

            return $cart->load('items.itemable');
        }

        if (! $sessionId) {
            throw new InvalidArgumentException('Session ID wajib diisi untuk cart guest.');
        }

        $cart = Cart::active()
            ->forGuest($sessionId)
            ->orderByDesc('updated_at')
            ->first();

        if (! $cart) {
            $cart = Cart::create([
                'customer_id' => null,
                'session_id' => $sessionId,
                'expires_at' => $expiresAt,
            ]);
        }

        return $cart->load('items.itemable');
    }

    public function mergeGuestCartOnLogin(User $user, string $sessionId): void
    {
        $guestCart = Cart::active()
            ->forGuest($sessionId)
            ->first();

        if (! $guestCart || $guestCart->items->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($user, $guestCart) {
            $userCart = Cart::active()
                ->forCustomer($user->id)
                ->orderByDesc('updated_at')
                ->first();

            if (! $userCart) {
                $guestCart->update([
                    'customer_id' => $user->id,
                    'session_id' => null,
                ]);
                $this->recalculateTotals($guestCart->fresh(['items.itemable']));

                return;
            }

            foreach ($guestCart->items as $guestItem) {
                $existing = $userCart->items()
                    ->where('itemable_type', $guestItem->itemable_type)
                    ->where('itemable_id', $guestItem->itemable_id)
                    ->first();

                if ($existing) {
                    $newQty = $existing->qty + $guestItem->qty;
                    $this->validateStock($guestItem->itemable, $newQty);
                    $existing->update(['qty' => $newQty]);
                } else {
                    $this->validateStock($guestItem->itemable, $guestItem->qty);
                    $guestItem->update(['cart_id' => $userCart->id]);
                }
            }

            $this->recalculateTotals($userCart->fresh(['items.itemable']));
            $guestCart->delete();
        });
    }

    public function addItem(Cart $cart, string $itemableType, int $itemableId, int $qty, ?string $notes = null): CartItem
    {
        $itemable = $this->resolveItem($itemableType, $itemableId);

        return DB::transaction(function () use ($cart, $itemable, $qty, $notes) {
            $existing = $cart->items()
                ->where('itemable_type', $itemable->getMorphClass())
                ->where('itemable_id', $itemable->getKey())
                ->first();

            $newQty = ($existing?->qty ?? 0) + $qty;

            $this->validateStock($itemable, $newQty);

            $priceSnapshot = $this->resolvePrice($itemable);

            if ($existing) {
                $existing->update(['qty' => $newQty]);

                $cartItem = $existing;
            } else {
                $cartItem = $cart->items()->create([
                    'itemable_type' => $itemable->getMorphClass(),
                    'itemable_id' => $itemable->getKey(),
                    'qty' => $qty,
                    'price_snapshot' => $priceSnapshot,
                    'notes' => $notes,
                ]);
            }

            $cart->touch();
            $this->recalculateTotals($cart->fresh(['items.itemable']));

            return $cartItem->load('itemable');
        });
    }

    public function updateItemQty(CartItem $item, int $qty): CartItem
    {
        if ($qty <= 0) {
            $item->delete();
            $this->recalculateTotals($item->cart->fresh(['items.itemable']));

            return $item;
        }

        $this->validateStock($item->itemable, $qty);

        $item->update(['qty' => $qty]);
        $item->cart->touch();
        $this->recalculateTotals($item->cart->fresh(['items.itemable']));

        return $item->fresh('itemable');
    }

    public function removeItem(CartItem $item): Cart
    {
        $cart = $item->cart;
        $item->delete();
        $cart->touch();
        $this->recalculateTotals($cart->fresh(['items.itemable']));

        return $cart->fresh(['items.itemable']);
    }

    public function clear(Cart $cart): Cart
    {
        DB::transaction(function () use ($cart) {
            $cart->items()->delete();
            $cart->update([
                'coupon_code' => null,
                'discount' => 0,
            ]);
            $this->recalculateTotals($cart->fresh(['items.itemable']));
        });

        return $cart->fresh(['items.itemable']);
    }

    public function applyCoupon(Cart $cart, string $code): Cart
    {
        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (! $coupon) {
            throw new InvalidArgumentException('Kode coupon tidak ditemukan.');
        }

        if (! $coupon->is_valid) {
            throw new InvalidArgumentException('Coupon tidak aktif atau sudah kadaluarsa.');
        }

        if ($coupon->is_first_order_only && $cart->customer_id) {
            $hasOrdered = $cart->customer->orders()->exists();
            if ($hasOrdered) {
                throw new InvalidArgumentException('Coupon ini hanya untuk pelanggan baru.');
            }
        }

        $subtotal = (float) $cart->subtotal;

        if ($coupon->min_order_amount && $subtotal < (float) $coupon->min_order_amount) {
            throw new InvalidArgumentException(
                'Minimum order untuk coupon ini adalah Rp '.number_format((float) $coupon->min_order_amount, 0, ',', '.')
            );
        }

        $discount = $coupon->calculateDiscount($subtotal);

        $cart->update([
            'coupon_code' => $coupon->code,
            'discount' => $discount,
        ]);

        $this->recalculateTotals($cart->fresh(['items.itemable']));

        return $cart->fresh(['items.itemable', 'customer']);
    }

    public function removeCoupon(Cart $cart): Cart
    {
        $cart->update([
            'coupon_code' => null,
            'discount' => 0,
        ]);
        $this->recalculateTotals($cart->fresh(['items.itemable']));

        return $cart->fresh(['items.itemable']);
    }

    public function recalculateTotals(Cart $cart): Cart
    {
        $cart->loadMissing('items');

        $subtotal = (float) $cart->items->sum(fn ($i) => $i->qty * (float) $i->price_snapshot);

        $discount = 0.0;
        if ($cart->coupon_code) {
            $coupon = Coupon::where('code', $cart->coupon_code)->first();
            if ($coupon && $coupon->is_valid) {
                $discount = $coupon->calculateDiscount($subtotal);
            } else {
                $cart->coupon_code = null;
            }
        }

        $tax = 0.0;
        $shipping = (float) ($cart->shipping_cost ?? 0);

        $total = max(0, $subtotal - $discount + $tax + $shipping);

        $cart->update([
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
        ]);

        return $cart;
    }

    public function calculateShipping(Cart $cart, string $city, string $courier = 'jne'): array
    {
        $subtotal = (float) $cart->subtotal;

        $freeThreshold = (float) config('karteks.shipping.free_shipping_threshold', 0);

        $baseOptions = [
            ['code' => 'REG', 'name' => 'Reguler', 'eta_days' => 3],
            ['code' => 'YES', 'name' => 'Yakin Esok Sampai', 'eta_days' => 1],
            ['code' => 'OKE', 'name' => 'Ongkos Kirim Ekonomis', 'eta_days' => 5],
        ];

        $results = [];
        foreach ($baseOptions as $opt) {
            $cost = match ($courier) {
                'jne' => match ($opt['code']) {
                    'YES' => 25000,
                    'REG' => 18000,
                    'OKE' => 12000,
                },
                'pos' => match ($opt['code']) {
                    'YES' => 22000,
                    'REG' => 16000,
                    'OKE' => 11000,
                },
                'tiki' => match ($opt['code']) {
                    'YES' => 26000,
                    'REG' => 19000,
                    'OKE' => 13000,
                },
                default => 18000,
            };

            $isFree = $freeThreshold > 0 && $subtotal >= $freeThreshold;

            $results[] = [
                'courier' => strtoupper($courier),
                'service' => $opt['code'],
                'name' => $opt['name'],
                'eta_days' => $opt['eta_days'],
                'cost' => $isFree ? 0 : $cost,
                'is_free' => $isFree,
                'free_threshold' => $isFree ? $freeThreshold : null,
            ];
        }

        return $results;
    }

    public function generateSessionId(): string
    {
        return (string) Str::uuid();
    }

    protected function validateStock(Model $itemable, int $requestedQty): void
    {
        if ($itemable instanceof Product) {
            if ($itemable->manage_stock && ! $itemable->allow_backorder) {
                $available = (int) $itemable->stock_qty;
                if ($requestedQty > $available) {
                    throw new InvalidArgumentException(
                        "Stok tidak cukup. Tersedia: {$available}, diminta: {$requestedQty}."
                    );
                }
            }
        } elseif ($itemable instanceof ProductVariation) {
            $available = max(0, (int) $itemable->stock_qty - (int) $itemable->reserved_qty);
            if ($requestedQty > $available) {
                throw new InvalidArgumentException(
                    "Stok variasi tidak cukup. Tersedia: {$available}, diminta: {$requestedQty}."
                );
            }
        }
    }

    protected function resolveItem(string $type, int $id): Model
    {
        $modelClass = match ($type) {
            'product' => Product::class,
            'variation' => ProductVariation::class,
            default => throw new InvalidArgumentException("Tipe item tidak valid: {$type}"),
        };

        $item = $modelClass::find($id);
        if (! $item) {
            throw new InvalidArgumentException('Produk tidak ditemukan.');
        }

        if ($item instanceof Product && ! ($item->status === 'published' || $item->status === 'active')) {
            throw new InvalidArgumentException('Produk tidak aktif.');
        }

        if ($item instanceof ProductVariation && ! $item->is_active) {
            throw new InvalidArgumentException('Variasi produk tidak aktif.');
        }

        return $item;
    }

    protected function resolvePrice(Model $itemable): float
    {
        if ($itemable instanceof Product || $itemable instanceof ProductVariation) {
            return (float) ($itemable->sale_price > 0 ? $itemable->sale_price : $itemable->price);
        }

        return 0.0;
    }
}