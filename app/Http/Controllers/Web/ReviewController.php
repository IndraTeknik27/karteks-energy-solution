<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Review\StoreReviewRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function create(Request $request, Product $product)
    {
        $user = $request->user();

        if (! $this->canReview($user->id, $product->id)) {
            return redirect()
                ->route('catalog.show', $product->slug)
                ->withErrors(['review' => 'Anda hanya bisa mengulas produk yang sudah Anda beli dan diterima.']);
        }

        $existing = Review::where('customer_id', $user->id)
            ->where('product_id', $product->id)
            ->whereNull('parent_id')
            ->first();

        if ($existing) {
            return redirect()
                ->route('dashboard.review.edit', $existing->id)
                ->with('info', 'Anda sudah pernah mengulas produk ini. Silakan edit ulasan Anda.');
        }

        return view('dashboard.reviews.create', compact('product'));
    }

    public function store(StoreReviewRequest $request, Product $product)
    {
        $user = $request->user();
        $data = $request->validated();

        if (! $this->canReview($user->id, $product->id)) {
            return back()
                ->withErrors(['review' => 'Anda hanya bisa mengulas produk yang sudah Anda beli dan diterima.'])
                ->withInput();
        }

        $review = Review::create([
            'product_id' => $product->id,
            'customer_id' => $user->id,
            'rating' => (int) $data['rating'],
            'title' => $data['title'] ?? null,
            'content' => $data['content'],
            'is_approved' => true,
            'is_verified_purchase' => true,
            'helpful_count' => 0,
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('dashboard.orders')
            ->with('success', 'Terima kasih! Ulasan Anda telah dipublikasikan.');
    }

    public function edit(Request $request, Review $review)
    {
        $user = $request->user();

        abort_unless($review->customer_id === $user->id, 403);

        $product = $review->product;
        return view('dashboard.reviews.edit', compact('review', 'product'));
    }

    public function update(StoreReviewRequest $request, Review $review)
    {
        $user = $request->user();
        abort_unless($review->customer_id === $user->id, 403);

        $data = $request->validated();
        $review->update([
            'rating' => (int) $data['rating'],
            'title' => $data['title'] ?? null,
            'content' => $data['content'],
            'is_approved' => true,
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('dashboard.review.edit', $review->id)
            ->with('success', 'Ulasan berhasil diperbarui.');
    }

    public function destroy(Request $request, Review $review)
    {
        $user = $request->user();
        abort_unless($review->customer_id === $user->id, 403);

        $review->delete();

        return redirect()
            ->route('dashboard.orders')
            ->with('success', 'Ulasan berhasil dihapus.');
    }

    public function myReviews(Request $request)
    {
        $user = $request->user();
        $reviews = Review::where('customer_id', $user->id)
            ->with('product')
            ->latest('created_at')
            ->paginate(10);

        return view('dashboard.reviews.index', compact('reviews'));
    }

    protected function canReview(int $customerId, int $productId): bool
    {
        return Order::where('customer_id', $customerId)
            ->whereIn('status', ['delivered', 'completed'])
            ->whereHas('items', function ($q) use ($productId) {
                $q->where('itemable_type', \App\Models\Product::class)
                    ->where('itemable_id', $productId);
            })
            ->exists();
    }
}