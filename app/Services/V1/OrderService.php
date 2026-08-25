<?php

namespace App\Services\V1;

use App\Mail\Order\OrderDeliveredMail;
use App\Mail\Order\OrderPaidMail;
use App\Mail\Order\OrderPlacedMail;
use App\Mail\Order\OrderShippedMail;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class OrderService
{
    public function placeOrder(User $user, array $data, $cart): Order
    {
        if ($cart->items->isEmpty()) {
            throw new InvalidArgumentException('Cart kosong. Tidak dapat membuat order.');
        }

        $shippingAddress = Address::where('customer_id', $user->id)
            ->where('id', $data['shipping_address_id'])
            ->first();

        if (! $shippingAddress) {
            throw new InvalidArgumentException('Alamat pengiriman tidak ditemukan atau bukan milik Anda.');
        }

        $order = DB::transaction(function () use ($user, $data, $cart, $shippingAddress) {
            $order = $this->createOrderRecord($user, $data, $cart, $shippingAddress);
            $this->createOrderItems($order, $cart);
            $this->reserveStock($order);
            $this->recordCouponUsage($order, $user);
            $this->recordInitialStatusHistory($order, $user);
            $this->clearCart($cart);

            return $order->load(['items.itemable', 'statusHistories', 'customer']);
        });

        // FASE 4.5: send branded OrderPlaced email (queued)
        $this->sendOrderEmail($order->fresh(['items.itemable', 'customer']), 'placed');

        return $order;
    }

    public function cancelOrder(Order $order, string $reason, ?User $changedBy = null): Order
    {
        if (! $this->canCancel($order)) {
            throw new InvalidArgumentException(
                "Order dengan status {$order->status} tidak dapat dibatalkan."
            );
        }

        return DB::transaction(function () use ($order, $reason, $changedBy) {
            $previousStatus = $order->status;
            $order->update([
                'status' => OrderStatusHistory::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'admin_notes' => trim(($order->admin_notes ?? '')."\n\nCustomer cancel: {$reason}"),
            ]);

            $this->releaseReservedStock($order);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $previousStatus,
                'to_status' => OrderStatusHistory::STATUS_CANCELLED,
                'note' => $reason,
                'changed_by' => $changedBy?->id,
                'changed_by_role' => $changedBy ? ($changedBy->isAdmin() ? 'admin' : 'customer') : 'customer',
            ]);

            return $order->fresh(['items.itemable', 'statusHistories']);
        });
    }

    public function confirmDelivery(Order $order, User $customer): Order
    {
        if ($order->customer_id !== $customer->id) {
            throw new InvalidArgumentException('Order ini bukan milik Anda.');
        }

        if ($order->status !== OrderStatusHistory::STATUS_SHIPPED) {
            throw new InvalidArgumentException(
                "Order status {$order->status} belum siap untuk konfirmasi delivery."
            );
        }

        return DB::transaction(function () use ($order, $customer) {
            $previousStatus = $order->status;
            $order->update([
                'status' => OrderStatusHistory::STATUS_DELIVERED,
                'delivered_at' => now(),
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $previousStatus,
                'to_status' => OrderStatusHistory::STATUS_DELIVERED,
                'note' => 'Customer konfirmasi barang diterima.',
                'changed_by' => $customer->id,
                'changed_by_role' => 'customer',
            ]);

            $this->markCompletedIfEligible($order, $customer);

            return $order->fresh(['items', 'statusHistories']);
        });
    }

    public function canCancel(Order $order): bool
    {
        return in_array($order->status, [
            OrderStatusHistory::STATUS_PENDING_PAYMENT,
            OrderStatusHistory::STATUS_PAYMENT_PENDING,
            OrderStatusHistory::STATUS_PAID,
            OrderStatusHistory::STATUS_PROCESSING,
        ], true);
    }

    public function preview(User $user, $cart, ?Address $shippingAddress, ?string $courier = null, ?string $couponCode = null): array
    {
        $items = $cart->items->map(function ($item) {
            $itemable = $item->itemable;
            $available = $this->getAvailableStock($itemable);
            $price = (float) $item->price_snapshot;

            return [
                'cart_item_id' => $item->id,
                'itemable_type' => $item->itemable_type,
                'itemable_id' => $item->itemable_id,
                'name' => $itemable?->name ?? 'Produk',
                'sku' => $itemable?->sku,
                'image' => $itemable ? (
                    method_exists($itemable, 'getFeaturedImageUrl')
                        ? $itemable->featuredImageUrl
                        : (method_exists($itemable, 'getFirstMediaUrl')
                            ? ($itemable->getFirstMediaUrl('gallery') ?: $itemable->getFirstMediaUrl('featured'))
                            : null)
                ) : null,
                'qty' => $item->qty,
                'price' => $price,
                'price_formatted' => 'Rp '.number_format($price, 0, ',', '.'),
                'subtotal' => (float) ($item->qty * $price),
                'subtotal_formatted' => 'Rp '.number_format((float) ($item->qty * $price), 0, ',', '.'),
                'available_stock' => $available,
                'stock_ok' => $item->qty <= $available,
                'notes' => $item->notes,
            ];
        });

        $subtotal = (float) $items->sum('subtotal');
        $discount = 0.0;
        $couponInfo = null;

        if ($couponCode) {
            $coupon = Coupon::where('code', strtoupper($couponCode))->first();
            if ($coupon && $coupon->is_valid) {
                $couponInfo = [
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'type' => $coupon->type,
                    'value' => (float) $coupon->value,
                    'min_order_amount' => $coupon->min_order_amount ? (float) $coupon->min_order_amount : null,
                    'max_discount_amount' => $coupon->max_discount_amount ? (float) $coupon->max_discount_amount : null,
                ];
                if (! $coupon->min_order_amount || $subtotal >= (float) $coupon->min_order_amount) {
                    $discount = $coupon->calculateDiscount($subtotal);
                }
            }
        } elseif ($cart->coupon_code) {
            $coupon = Coupon::where('code', $cart->coupon_code)->first();
            if ($coupon && $coupon->is_valid) {
                $couponInfo = [
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'type' => $coupon->type,
                    'value' => (float) $coupon->value,
                ];
                $discount = (float) $cart->discount;
            }
        }

        $tax = 0.0;
        $shippingOptions = $shippingAddress
            ? (new CartService)->calculateShipping($cart, $shippingAddress->city, $courier ?? 'jne')
            : [];

        $preview = [
            'cart_id' => $cart->id,
            'items' => $items,
            'item_count' => (int) $items->sum('qty'),
            'subtotal' => $subtotal,
            'subtotal_formatted' => 'Rp '.number_format($subtotal, 0, ',', '.'),
            'discount' => $discount,
            'discount_formatted' => 'Rp '.number_format($discount, 0, ',', '.'),
            'tax' => $tax,
            'tax_formatted' => 'Rp '.number_format($tax, 0, ',', '.'),
            'coupon' => $couponInfo,
            'shipping_address' => $shippingAddress ? [
                'id' => $shippingAddress->id,
                'label' => $shippingAddress->label,
                'recipient' => $shippingAddress->recipient,
                'phone' => $shippingAddress->phone,
                'address_line_1' => $shippingAddress->address_line_1,
                'city' => $shippingAddress->city,
                'province' => $shippingAddress->province,
                'postal_code' => $shippingAddress->postal_code,
                'is_primary' => (bool) $shippingAddress->is_primary,
            ] : null,
            'shipping_options' => $shippingOptions,
            'all_stock_ok' => $items->every(fn ($i) => $i['stock_ok']),
        ];

        return $preview;
    }

    public function generateOrderNumber(): string
    {
        $prefix = config('karteks.numbering.order.prefix', 'ORD');
        $padding = (int) config('karteks.numbering.order.padding', 5);

        $today = now()->format('Ymd');

        $lastOrder = Order::where('order_number', 'like', "{$prefix}-{$today}-%")
            ->orderByDesc('id')
            ->first();

        if ($lastOrder) {
            $parts = explode('-', $lastOrder->order_number);
            $sequence = (int) end($parts);
            $sequence++;
        } else {
            $sequence = 1;
        }

        return sprintf('%s-%s-%0'.$padding.'d', $prefix, $today, $sequence);
    }

    protected function createOrderRecord(User $user, array $data, $cart, Address $shippingAddress): Order
    {
        return Order::create([
            'order_number' => $this->generateOrderNumber(),
            'customer_id' => $user->id,
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'],

            'status' => OrderStatusHistory::STATUS_PENDING_PAYMENT,
            'payment_method' => $data['payment_method'],

            'subtotal' => (float) $cart->subtotal,
            'discount' => 0,
            'coupon_code' => $cart->coupon_code,
            'coupon_discount' => (float) $cart->discount,

            'tax' => 0,
            'shipping_cost' => (float) $data['shipping_cost'],
            'total' => (float) ($cart->subtotal - $cart->discount + (float) $data['shipping_cost']),

            'shipping_address' => [
                'id' => $shippingAddress->id,
                'recipient' => $shippingAddress->recipient,
                'phone' => $shippingAddress->phone,
                'address_line_1' => $shippingAddress->address_line_1,
                'address_line_2' => $shippingAddress->address_line_2,
                'village' => $shippingAddress->village,
                'district' => $shippingAddress->district,
                'city' => $shippingAddress->city,
                'province' => $shippingAddress->province,
                'postal_code' => $shippingAddress->postal_code,
                'notes' => $shippingAddress->notes,
            ],
            'billing_address' => $this->resolveBillingAddress($user, $data, $shippingAddress),
            'shipping_courier' => $data['shipping_courier'],
            'shipping_service' => $data['shipping_service'],
            'shipping_tracking_number' => null,

            'customer_notes' => $data['customer_notes'] ?? null,
            'expires_at' => now()->addHours(24),
        ]);
    }

    protected function createOrderItems(Order $order, $cart): void
    {
        foreach ($cart->items as $cartItem) {
            $itemable = $cartItem->itemable;

            $image = null;
            if ($itemable) {
                $image = method_exists($itemable, 'getFeaturedImageUrl')
                    ? $itemable->featuredImageUrl
                    : (method_exists($itemable, 'getFirstMediaUrl')
                        ? ($itemable->getFirstMediaUrl('gallery') ?: $itemable->getFirstMediaUrl('featured'))
                        : null);
            }

            OrderItem::create([
                'order_id' => $order->id,
                'itemable_type' => $cartItem->itemable_type,
                'itemable_id' => $cartItem->itemable_id,
                'name' => $itemable?->name ?? 'Produk',
                'sku' => $itemable?->sku,
                'image' => $image,
                'price' => (float) $cartItem->price_snapshot,
                'qty' => $cartItem->qty,
                'variation_attributes' => $itemable instanceof \App\Models\ProductVariation
                    ? ($itemable->attributes ?? [])
                    : null,
                'notes' => $cartItem->notes,
            ]);
        }
    }

    protected function reserveStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $itemable = $item->itemable;
            $qty = (int) $item->qty;
            $beforeQty = 0;
            $afterQty = 0;
            $productId = null;
            $variationId = null;

            if ($itemable instanceof \App\Models\Product) {
                $productId = $itemable->id;
                $beforeQty = (int) $itemable->stock_qty;
                $itemable->update([
                    'stock_qty' => max(0, $beforeQty - $qty),
                ]);
                $afterQty = max(0, $beforeQty - $qty);
            } elseif ($itemable instanceof \App\Models\ProductVariation) {
                $variationId = $itemable->id;
                $productId = $itemable->product_id;
                $beforeQty = (int) $itemable->stock_qty;
                $itemable->update([
                    'stock_qty' => max(0, $beforeQty - $qty),
                    'reserved_qty' => (int) $itemable->reserved_qty + $qty,
                ]);
                $afterQty = max(0, $beforeQty - $qty);
            }

            StockMovement::create([
                'product_id' => $productId,
                'variation_id' => $variationId,
                'warehouse_id' => \App\Models\Warehouse::where('is_active', true)->value('id'),
                'type' => 'reserve',
                'qty' => -$qty,
                'before_qty' => $beforeQty,
                'after_qty' => $afterQty,
                'reference_type' => Order::class,
                'reference_id' => $order->id,
                'user_id' => $order->customer_id,
                'note' => "Reservasi untuk order {$order->order_number}",
                'created_at' => now(),
            ]);
        }
    }

    protected function releaseReservedStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $itemable = $item->itemable;
            $qty = (int) $item->qty;

            if ($itemable instanceof \App\Models\Product) {
                $beforeQty = (int) $itemable->stock_qty;
                $itemable->update([
                    'stock_qty' => $beforeQty + $qty,
                ]);
            } elseif ($itemable instanceof \App\Models\ProductVariation) {
                $beforeQty = (int) $itemable->stock_qty;
                $itemable->update([
                    'stock_qty' => $beforeQty + $qty,
                    'reserved_qty' => max(0, (int) $itemable->reserved_qty - $qty),
                ]);
            }

            StockMovement::create([
                'product_id' => $itemable?->id ?? $item->itemable_id,
                'variation_id' => $itemable instanceof \App\Models\ProductVariation ? $itemable->id : null,
                'warehouse_id' => \App\Models\Warehouse::where('is_active', true)->value('id'),
                'type' => 'release',
                'qty' => $qty,
                'before_qty' => $itemable ? (int) $itemable->stock_qty : 0,
                'after_qty' => $itemable ? (int) $itemable->stock_qty + 0 : 0,
                'reference_type' => Order::class,
                'reference_id' => $order->id,
                'user_id' => $order->customer_id,
                'note' => "Release reservasi untuk order {$order->order_number} (cancelled)",
                'created_at' => now(),
            ]);
        }
    }

    protected function recordCouponUsage(Order $order, User $user): void
    {
        if (! $order->coupon_code || $order->coupon_discount <= 0) {
            return;
        }

        $coupon = Coupon::where('code', $order->coupon_code)->first();
        if (! $coupon) {
            return;
        }

        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'customer_id' => $user->id,
            'order_id' => $order->id,
            'discount_amount' => $order->coupon_discount,
        ]);

        $coupon->increment('used_count');
    }

    protected function recordInitialStatusHistory(Order $order, User $user): void
    {
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => null,
            'to_status' => OrderStatusHistory::STATUS_PENDING_PAYMENT,
            'note' => 'Order dibuat. Menunggu pembayaran.',
            'changed_by' => $user->id,
            'changed_by_role' => 'customer',
        ]);
    }

    protected function clearCart($cart): void
    {
        $cart->items()->delete();
        $cart->update([
            'coupon_code' => null,
            'discount' => 0,
            'subtotal' => 0,
            'tax' => 0,
            'shipping_cost' => 0,
            'total' => 0,
        ]);
    }

    protected function resolveBillingAddress(User $user, array $data, Address $shipping): array
    {
        if (! empty($data['billing_same_as_shipping'])) {
            return [
                'same_as_shipping' => true,
                'recipient' => $shipping->recipient,
                'phone' => $shipping->phone,
                'address_line_1' => $shipping->address_line_1,
                'address_line_2' => $shipping->address_line_2,
                'village' => $shipping->village,
                'district' => $shipping->district,
                'city' => $shipping->city,
                'province' => $shipping->province,
                'postal_code' => $shipping->postal_code,
            ];
        }

        if (! empty($data['billing_address'])) {
            return array_merge(['same_as_shipping' => false], $data['billing_address']);
        }

        return ['same_as_shipping' => true];
    }

    protected function markCompletedIfEligible(Order $order, User $customer): void
    {
        $order->refresh();
        if ($order->status === OrderStatusHistory::STATUS_DELIVERED) {
            $previousStatus = $order->status;
            $order->update([
                'status' => OrderStatusHistory::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $previousStatus,
                'to_status' => OrderStatusHistory::STATUS_COMPLETED,
                'note' => 'Order selesai.',
                'changed_by' => $customer->id,
                'changed_by_role' => 'customer',
            ]);
        }
    }

    public function confirmPaymentByAdmin(Order $order, User $admin, ?string $notes = null): Order
    {
        if ($order->paid_at) {
            throw new InvalidArgumentException('Order sudah ditandai lunas.');
        }

        if (! in_array($order->status, [OrderStatusHistory::STATUS_PENDING_PAYMENT, OrderStatusHistory::STATUS_PAYMENT_PENDING], true)) {
            throw new InvalidArgumentException(
                "Order status '{$order->status}' tidak bisa ditandai lunas."
            );
        }

        return DB::transaction(function () use ($order, $admin, $notes) {
            $previousStatus = $order->status;
            $order->update([
                'status' => OrderStatusHistory::STATUS_PAID,
                'paid_at' => now(),
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $previousStatus,
                'to_status' => OrderStatusHistory::STATUS_PAID,
                'note' => $notes ?? 'Pembayaran dikonfirmasi manual oleh admin.',
                'changed_by' => $admin->id,
                'changed_by_role' => 'admin',
            ]);

            return $order->fresh();
        });
    }

    public function adminTransition(
        Order $order,
        string $newStatus,
        User $admin,
        ?string $notes = null,
        ?string $trackingNumber = null
    ): Order {
        $allowedFrom = [
            'processing' => [OrderStatusHistory::STATUS_PENDING_PAYMENT, OrderStatusHistory::STATUS_PAYMENT_PENDING, OrderStatusHistory::STATUS_PAID],
            'ready_to_ship' => [OrderStatusHistory::STATUS_PROCESSING, OrderStatusHistory::STATUS_PAID],
            'shipped' => [OrderStatusHistory::STATUS_READY_TO_SHIP],
            'delivered' => [OrderStatusHistory::STATUS_SHIPPED],
            'completed' => [OrderStatusHistory::STATUS_DELIVERED],
            'cancelled' => [OrderStatusHistory::STATUS_PENDING_PAYMENT, OrderStatusHistory::STATUS_PAYMENT_PENDING, OrderStatusHistory::STATUS_PAID, OrderStatusHistory::STATUS_PROCESSING, OrderStatusHistory::STATUS_READY_TO_SHIP],
            'refunded' => [OrderStatusHistory::STATUS_PAID, OrderStatusHistory::STATUS_PROCESSING, OrderStatusHistory::STATUS_READY_TO_SHIP, OrderStatusHistory::STATUS_SHIPPED, OrderStatusHistory::STATUS_DELIVERED, OrderStatusHistory::STATUS_COMPLETED],
            'failed' => [OrderStatusHistory::STATUS_PENDING_PAYMENT, OrderStatusHistory::STATUS_PAYMENT_PENDING],
        ];

        if (! in_array($order->status, $allowedFrom[$newStatus] ?? [], true)) {
            throw new InvalidArgumentException(
                "Tidak bisa transisi dari '{$order->status}' ke '{$newStatus}'."
            );
        }

        return DB::transaction(function () use ($order, $newStatus, $admin, $notes, $trackingNumber) {
            $previousStatus = $order->status;
            $updates = ['status' => $newStatus];

            match ($newStatus) {
                'shipped' => $updates['shipped_at'] = now(),
                'delivered' => $updates['delivered_at'] = now(),
                'completed' => $updates['completed_at'] = now(),
                'cancelled' => $updates['cancelled_at'] = now(),
                default => null,
            };

            if ($trackingNumber) {
                $updates['shipping_tracking_number'] = $trackingNumber;
            }

            $order->update($updates);

            if ($newStatus === 'cancelled') {
                $this->releaseReservedStock($order);
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $previousStatus,
                'to_status' => $newStatus,
                'note' => $notes ?? "Status diubah oleh admin ke '{$newStatus}'.",
                'changed_by' => $admin->id,
                'changed_by_role' => 'admin',
            ]);

            $order = $order->fresh();

            // FASE 4.5: send branded email per status transition
            $this->sendOrderEmailOnTransition($order, $newStatus);

            return $order;
        });
    }

    /**
     * FASE 4.5: send branded Order email after placeOrder.
     */
    protected function sendOrderEmail(Order $order, string $stage): void
    {
        try {
            $customer = $order->customer;
            if (! $customer || ! $customer->email) {
                return;
            }
            $mail = match ($stage) {
                'placed' => new OrderPlacedMail($order),
                default => null,
            };
            if ($mail) {
                Mail::to($customer->email, $customer->name)->queue($mail);
            }
        } catch (\Throwable $e) {
            // Log tapi jangan gagalkan order flow
            \Log::warning('Order email gagal dikirim: '.$e->getMessage(), ['order_id' => $order->id]);
        }
    }

    /**
     * FASE 4.5: send branded email per adminTransition.
     */
    protected function sendOrderEmailOnTransition(Order $order, string $newStatus): void
    {
        try {
            $customer = $order->customer;
            if (! $customer || ! $customer->email) {
                return;
            }
            $mail = match ($newStatus) {
                'paid' => new OrderPaidMail($order),
                'shipped' => new OrderShippedMail($order),
                'delivered' => new OrderDeliveredMail($order),
                default => null,
            };
            if ($mail) {
                Mail::to($customer->email, $customer->name)->queue($mail);
            }
        } catch (\Throwable $e) {
            \Log::warning('Order transition email gagal dikirim: '.$e->getMessage(), ['order_id' => $order->id]);
        }
    }

    protected function getAvailableStock(Model $itemable): int
    {
        if (! $itemable) {
            return 0;
        }

        if ($itemable instanceof \App\Models\Product) {
            return (int) $itemable->stock_qty;
        }

        if ($itemable instanceof \App\Models\ProductVariation) {
            return max(0, (int) $itemable->stock_qty - (int) $itemable->reserved_qty);
        }

        return PHP_INT_MAX;
    }
}