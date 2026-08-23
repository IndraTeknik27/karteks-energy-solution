<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Product;
use App\Models\Service;
use App\Models\Testimonial;

class HomeController extends Controller
{
    public function index()
    {
        $heroBanners = Banner::active()->position('home_hero')->orderBy('sort')->limit(5)->get();
        $secondaryBanners = Banner::active()->position('home_secondary')->orderBy('sort')->limit(3)->get();

        $featuredProducts = Product::query()
            ->where('status', 'published')
            ->where('is_featured', true)
            ->with(['category', 'brand', 'images'])
            ->latest('published_at')
            ->limit(8)
            ->get();

        $newArrivals = Product::query()
            ->where('status', 'published')
            ->where('is_new_arrival', true)
            ->with(['category', 'brand', 'images'])
            ->latest('published_at')
            ->limit(4)
            ->get();

        $bestSellers = Product::query()
            ->where('status', 'published')
            ->where('is_bestseller', true)
            ->with(['category', 'brand', 'images'])
            ->orderByDesc('sales_count')
            ->limit(4)
            ->get();

        $topCategories = Category::query()
            ->active()
            ->roots()
            ->withCount(['products' => fn ($q) => $q->where('status', 'published')])
            ->with(['children' => fn ($q) => $q->active()->limit(6)])
            ->orderBy('sort')
            ->limit(8)
            ->get();

        $featuredServices = Service::query()
            ->active()
            ->where('is_featured', true)
            ->with('category')
            ->orderBy('sort')
            ->limit(6)
            ->get();

        $testimonials = Testimonial::query()
            ->active()
            ->orderByDesc('is_featured')
            ->orderBy('sort')
            ->limit(6)
            ->get();

        $faqs = Faq::query()
            ->active()
            ->orderBy('sort')
            ->limit(6)
            ->get();

        $latestBlogs = Blog::query()
            ->published()
            ->with(['category', 'author'])
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('pages.home', compact(
            'heroBanners',
            'secondaryBanners',
            'featuredProducts',
            'newArrivals',
            'bestSellers',
            'topCategories',
            'featuredServices',
            'testimonials',
            'faqs',
            'latestBlogs',
        ));
    }
}