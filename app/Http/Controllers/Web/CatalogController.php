<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->where('status', 'published')
            ->with(['category', 'brand', 'images']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('category_slug')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category_slug));
        }
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->filled('brand_slug')) {
            $query->whereHas('brand', fn ($q) => $q->where('slug', $request->brand_slug));
        }
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
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
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'oldest' => $query->orderBy('published_at'),
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'popular' => $query->orderByDesc('views'),
            default => $query->latest('published_at'),
        };

        $perPage = min((int) $request->input('per_page', 12), 60);
        $products = $query->paginate($perPage)->withQueryString();

        $categories = Category::active()->roots()->orderBy('sort')->get();
        $brands = Brand::active()->orderBy('sort')->get();

        $currentCategory = null;
        if ($request->filled('category_id')) {
            $currentCategory = Category::find($request->category_id);
        } elseif ($request->filled('category_slug')) {
            $currentCategory = Category::where('slug', $request->category_slug)->first();
        }

        $currentBrand = null;
        if ($request->filled('brand_id')) {
            $currentBrand = Brand::find($request->brand_id);
        } elseif ($request->filled('brand_slug')) {
            $currentBrand = Brand::where('slug', $request->brand_slug)->first();
        }

        return view('pages.catalog.index', compact(
            'products',
            'categories',
            'brands',
            'currentCategory',
            'currentBrand',
        ));
    }

    public function show(string $slug)
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with([
                'category',
                'brand',
                'images' => fn ($q) => $q->orderBy('sort'),
                'variations' => fn ($q) => $q->where('is_active', true)->orderBy('sort'),
            ])
            ->first();

        if (! $product) {
            abort(404, 'Produk tidak ditemukan.');
        }

        $product->increment('views');

        $related = Product::query()
            ->where('status', 'published')
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($product) {
                $q->where('category_id', $product->category_id)
                    ->orWhere('brand_id', $product->brand_id);
            })
            ->with(['category', 'brand', 'images'])
            ->inRandomOrder()
            ->limit(4)
            ->get();

        $reviews = $product->reviews()
            ->where('is_approved', true)
            ->whereNull('parent_id')
            ->with('customer:id,name')
            ->latest('approved_at')
            ->paginate(5);

        $reviewStats = [
            'total' => $product->reviews()->where('is_approved', true)->count(),
            'average' => round((float) $product->reviews()->where('is_approved', true)->avg('rating'), 1),
        ];

        return view('pages.catalog.show', compact('product', 'related', 'reviews', 'reviewStats'));
    }
}