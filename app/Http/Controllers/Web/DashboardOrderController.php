<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\V1\OrderService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class DashboardOrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->with('items')
            ->latest('created_at')
            ->paginate(min((int) $request->input('per_page', 10), 30));

        return view('dashboard.orders.index', compact('orders'));
    }

    public function show(Request $request, string $orderNumber)
    {
        $order = $request->user()->orders()
            ->where('order_number', $orderNumber)
            ->with(['items.itemable', 'statusHistories'])
            ->first();

        if (! $order) {
            abort(404, 'Order tidak ditemukan.');
        }

        return view('dashboard.orders.show', compact('order'));
    }

    public function cancel(Request $request, string $orderNumber)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $order = $request->user()->orders()->where('order_number', $orderNumber)->first();
        if (! $order) {
            abort(404, 'Order tidak ditemukan.');
        }

        try {
            $this->orderService->cancelOrder($order, $data['reason'], $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['cancel' => $e->getMessage()]);
        }

        return redirect()->route('dashboard.orders.show', $order->order_number)
            ->with('success', "Order {$order->order_number} berhasil dibatalkan.");
    }

    public function invoice(Request $request, string $orderNumber)
    {
        $order = $request->user()->orders()
            ->where('order_number', $orderNumber)
            ->with('items')
            ->first();

        if (! $order) {
            abort(404, 'Order tidak ditemukan.');
        }

        return view('dashboard.orders.invoice', compact('order'));
    }
}