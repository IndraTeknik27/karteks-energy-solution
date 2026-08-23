<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->published()->with(['category', 'brand', 'images']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('category_slug')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category_slug));
        }
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->boolean('featured')) {
            $query->featured();
        }
        if ($request->boolean('bestseller')) {
            $query->where('is_bestseller', true);
        }
        if ($request->boolean('new')) {
            $query->where('is_new_arrival', true);
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->max_price);
        }

        $sort = $request->input('sort', 'latest');
        $query->when($sort === 'price_asc', fn ($q) => $q->orderBy('price', 'asc'))
            ->when($sort === 'price_desc', fn ($q) => $q->orderBy('price', 'desc'))
            ->when($sort === 'name_asc', fn ($q) => $q->orderBy('name', 'asc'))
            ->when($sort === 'popular', fn ($q) => $q->orderByDesc('views'))
            ->when($sort === 'latest', fn ($q) => $q->latest('published_at'));

        $perPage = min((int) $request->input('per_page', 15), 100);
        $products = $query->paginate($perPage);

        return $this->success($products, 'Products retrieved successfully');
    }

    public function featured()
    {
        $products = Product::published()->featured()
            ->with(['category', 'brand', 'images'])
            ->latest('published_at')
            ->limit(10)
            ->get();

        return $this->success($products, 'Featured products retrieved');
    }

    public function bestSellers()
    {
        $products = Product::published()->where('is_bestseller', true)
            ->with(['category', 'brand', 'images'])
            ->orderByDesc('sales_count')
            ->limit(10)
            ->get();

        return $this->success($products, 'Best sellers retrieved');
    }

    public function newArrivals()
    {
        $products = Product::published()->where('is_new_arrival', true)
            ->with(['category', 'brand', 'images'])
            ->latest('published_at')
            ->limit(10)
            ->get();

        return $this->success($products, 'New arrivals retrieved');
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->published()
            ->with([
                'category',
                'brand',
                'images' => fn ($q) => $q->orderBy('sort'),
                'variations' => fn ($q) => $q->where('is_active', true)->orderBy('sort'),
            ])
            ->first();

        if (! $product) {
            return $this->notFound('Product not found');
        }

        $product->increment('views');

        return $this->success($product, 'Product retrieved successfully');
    }

    public function related(string $slug)
    {
        $product = Product::where('slug', $slug)->published()->first();
        if (! $product) {
            return $this->notFound('Product not found');
        }

        $related = Product::published()
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($product) {
                $q->where('category_id', $product->category_id)
                    ->orWhere('brand_id', $product->brand_id);
            })
            ->with(['category', 'brand', 'images'])
            ->inRandomOrder()
            ->limit(8)
            ->get();

        return $this->success($related, 'Related products retrieved');
    }
}