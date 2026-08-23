<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function index(Request $request, string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)
            ->published()
            ->first();

        if (! $product) {
            return $this->notFound('Produk tidak ditemukan.');
        }

        $query = Review::query()
            ->where('product_id', $product->id)
            ->approved()
            ->parent()
            ->with(['customer:id,name,avatar', 'images']);

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->rating);
        }

        if ($request->boolean('verified_only')) {
            $query->where('is_verified_purchase', true);
        }

        $sort = $request->input('sort', 'recent');
        $query->when($sort === 'recent', fn ($q) => $q->latest('approved_at'))
            ->when($sort === 'helpful', fn ($q) => $q->orderByDesc('helpful_count'))
            ->when($sort === 'rating_high', fn ($q) => $q->orderByDesc('rating'))
            ->when($sort === 'rating_low', fn ($q) => $q->orderBy('rating'));

        $perPage = min((int) $request->input('per_page', 10), 50);
        $reviews = $query->paginate($perPage);

        $stats = Review::where('product_id', $product->id)
            ->approved()
            ->selectRaw('COUNT(*) as total, AVG(rating) as average')
            ->first();

        $distribution = Review::where('product_id', $product->id)
            ->approved()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $ratingDistribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = (int) ($distribution[$i] ?? 0);
            $ratingDistribution[] = [
                'rating' => $i,
                'count' => $count,
                'percentage' => $stats->total > 0
                    ? round(($count / $stats->total) * 100, 1)
                    : 0.0,
            ];
        }

        return $this->success([
            'reviews' => $reviews,
            'stats' => [
                'total' => (int) $stats->total,
                'average' => $stats->average ? round((float) $stats->average, 2) : 0,
                'distribution' => $ratingDistribution,
            ],
        ], 'Review produk.');
    }
}