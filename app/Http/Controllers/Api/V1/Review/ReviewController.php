<?php

namespace App\Http\Controllers\Api\V1\Review;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Api\V1\Review\StoreReviewRequest;
use App\Http\Resources\V1\ReviewResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Product $product): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (! $this->canReview($user->id, $product->id)) {
            throw ValidationException::withMessages([
                'product_id' => ['Anda hanya bisa mengulas produk yang sudah Anda beli dan diterima.'],
            ]);
        }

        $existing = Review::where('customer_id', $user->id)
            ->where('product_id', $product->id)
            ->whereNull('parent_id')
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'product_id' => ['Anda sudah pernah mengulas produk ini. Gunakan endpoint update.'],
            ]);
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

        return $this->success(
            new ReviewResource($review->load('customer:id,name')),
            'Ulasan berhasil dipublikasikan.',
            201,
        );
    }

    public function update(StoreReviewRequest $request, Review $review): JsonResponse
    {
        $user = $request->user();
        abort_if($review->customer_id !== $user->id, 403, 'Anda tidak memiliki akses ke ulasan ini.');

        $data = $request->validated();
        $review->update([
            'rating' => (int) $data['rating'],
            'title' => $data['title'] ?? null,
            'content' => $data['content'],
            'is_approved' => true,
            'approved_at' => now(),
        ]);

        return $this->success(
            new ReviewResource($review->fresh('customer:id,name')),
            'Ulasan berhasil diperbarui.',
        );
    }

    public function destroy(Request $request, Review $review): JsonResponse
    {
        $user = $request->user();
        abort_if($review->customer_id !== $user->id, 403, 'Anda tidak memiliki akses ke ulasan ini.');

        $review->delete();

        return $this->success(null, 'Ulasan berhasil dihapus.');
    }

    public function myReviews(Request $request): JsonResponse
    {
        $user = $request->user();
        $reviews = Review::where('customer_id', $user->id)
            ->with('product:id,name,slug')
            ->latest('created_at')
            ->paginate(min((int) $request->input('per_page', 15), 50));

        return $this->success(
            ReviewResource::collection($reviews),
            'Daftar ulasan saya.',
        );
    }

    protected function canReview(int $customerId, int $productId): bool
    {
        return Order::where('customer_id', $customerId)
            ->whereIn('status', ['delivered', 'completed'])
            ->whereHas('items', function ($q) use ($productId) {
                $q->where('itemable_type', Product::class)
                    ->where('itemable_id', $productId);
            })
            ->exists();
    }
}