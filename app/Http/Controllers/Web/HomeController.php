<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Faq;
use App\Services\V1\HomepageService;
use App\Services\V1\SeoService;

class HomeController extends Controller
{
    public function __construct(
        protected HomepageService $homepage,
        protected SeoService $seo,
    ) {}

    public function index()
    {
        // Load sections dari HomepageService (DB-driven + cache).
        $sections = $this->homepage->loadSections();

        // Banners popup (jika ada, tampilkan via Alpine.js state).
        $popupBanners = Banner::active()
            ->position(Banner::POSITION_POPUP)
            ->orderBy('sort')
            ->limit(1)
            ->get();

        // FAQ untuk footer section (fallback jika tidak ada section di DB).
        $faqs = Faq::query()->active()->orderBy('sort')->limit(6)->get();

        // Hero brand showcase kecil untuk hero utama (fallback jika sections kosong).
        $heroFeaturedProducts = $this->homepage->getBannersByPosition(Banner::POSITION_HOME_HERO, 0)->isNotEmpty()
            ? null
            : \App\Models\Product::query()
                ->where('status', 'published')
                ->where('is_featured', true)
                ->with(['category', 'brand'])
                ->latest('published_at')
                ->limit(4)
                ->get();

        // SEO meta tags untuk homepage
        $seoMeta = $this->seo->generateMeta(null, [
            'title' => 'KARTEKS ENERGY SOLUTION - Solusi Energi Terbarukan & Kendaraan Listrik',
            'description' => 'KARTEKS ENERGY SOLUTION - Solusi energi terbarukan, kendaraan listrik, custom battery, dan konsultasi profesional di Sulawesi Selatan.',
            'canonical' => route('home'),
        ]);

        // JSON-LD schemas: Organization + WebSite (homepage)
        $seoSchemas = [
            $this->seo->organizationJsonLd(),
            $this->seo->websiteJsonLd(),
        ];

        // FAQ schema jika ada FAQ (untuk SEO rich snippet)
        if ($faqs->isNotEmpty()) {
            $seoSchemas[] = $this->seo->faqJsonLd($faqs);
        }

        return view('pages.home', compact(
            'sections',
            'popupBanners',
            'faqs',
            'heroFeaturedProducts',
            'seoMeta',
            'seoSchemas',
        ));
    }
}