<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\Cache\CatalogSidebarCacheService;
use App\Services\V1\SeoService;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(
        protected SeoService $seo,
    ) {}
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

        // Sidebar data — cached 1 jam, invalidated by CategoryObserver + BrandObserver
        $categories = collect(CatalogSidebarCacheService::getCategories());
        $brands = collect(CatalogSidebarCacheService::getBrands());

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

        // SEO meta tags untuk catalog index (atau filtered category/brand)
        $seoEntity = $currentCategory ?? $currentBrand;
        $seoMeta = $this->seo->generateMeta($seoEntity, [
            'title' => $seoEntity ? null : 'Katalog Produk - KARTEKS ENERGY SOLUTION',
            'description' => $seoEntity ? null : 'Jelajahi koleksi lengkap produk KARTEKS: EV Car, EV Bike, Custom Battery, Solar Panel, dan lainnya.',
            'canonical' => url()->current(),
        ]);

        // Breadcrumb JSON-LD untuk filtered pages
        $seoSchemas = [];
        $breadcrumbItems = [
            ['name' => 'Beranda', 'url' => route('home')],
            ['name' => 'Produk', 'url' => route('catalog.index')],
        ];
        if ($currentCategory) {
            $breadcrumbItems[] = ['name' => $currentCategory->name, 'url' => route('catalog.index', ['category_slug' => $currentCategory->slug])];
        }
        if ($currentBrand) {
            $breadcrumbItems[] = ['name' => $currentBrand->name, 'url' => route('catalog.index', ['brand_slug' => $currentBrand->slug])];
        }
        $seoSchemas[] = $this->seo->breadcrumbJsonLd($breadcrumbItems);

        return view('pages.catalog.index', compact(
            'products',
            'categories',
            'brands',
            'currentCategory',
            'currentBrand',
            'seoMeta',
            'seoSchemas',
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

        // SEO meta tags untuk product detail
        $seoMeta = $this->seo->generateMeta($product);
        $seoSchemas = [$this->seo->productJsonLd($product)];

        // Breadcrumb JSON-LD
        $breadcrumbItems = [
            ['name' => 'Beranda', 'url' => route('home')],
            ['name' => 'Produk', 'url' => route('catalog.index')],
        ];
        if ($product->category) {
            $breadcrumbItems[] = ['name' => $product->category->name, 'url' => route('catalog.index', ['category_slug' => $product->category->slug])];
        }
        $breadcrumbItems[] = ['name' => $product->name, 'url' => route('catalog.show', $product->slug)];
        $seoSchemas[] = $this->seo->breadcrumbJsonLd($breadcrumbItems);

        return view('pages.catalog.show', compact('product', 'related', 'reviews', 'reviewStats', 'seoMeta', 'seoSchemas'));
    }
}