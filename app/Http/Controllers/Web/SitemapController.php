<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate dynamic sitemap.xml.
     * Cache TTL 6 jam (sitemap di-refresh ketika content published).
     */
    public function index(): Response
    {
        $xml = \Illuminate\Support\Facades\Cache::remember('sitemap:xml:v1', now()->addHours(6), function () {
            return $this->buildSitemap();
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }

    protected function buildSitemap(): string
    {
        $urls = collect();

        // Static pages
        $urls->push($this->urlNode(route('home'), '1.0', 'daily', now()));
        $urls->push($this->urlNode(route('catalog.index'), '0.9', 'daily'));
        $urls->push($this->urlNode(route('services.index'), '0.9', 'weekly'));
        $urls->push($this->urlNode(route('blog.index'), '0.8', 'daily'));
        $urls->push($this->urlNode(route('cart.index'), '0.3', 'monthly'));

        // Brands
        Brand::query()->active()->orderBy('sort')->each(function (Brand $brand) use ($urls) {
            $urls->push($this->urlNode(
                route('catalog.index', ['brand_slug' => $brand->slug]),
                '0.7',
                'weekly',
            ));
        });

        // Categories
        Category::query()->active()->orderBy('sort')->each(function (Category $category) use ($urls) {
            $urls->push($this->urlNode(
                route('catalog.index', ['category_slug' => $category->slug]),
                '0.7',
                'weekly',
            ));
        });

        // Products (published only)
        Product::query()
            ->where('status', 'published')
            ->orderByDesc('updated_at')
            ->each(function (Product $product) use ($urls) {
                $urls->push($this->urlNode(
                    route('catalog.show', $product->slug),
                    '0.8',
                    'weekly',
                    $product->updated_at,
                    ['product' => [
                        'price' => (float) ($product->sale_price ?: $product->price),
                        'currency' => config('karteks.locale.currency', 'IDR'),
                        'availability' => $product->manage_stock && $product->stock_qty <= 0 ? 'out of stock' : 'in stock',
                    ]],
                ));
            });

        // Services (active)
        Service::query()
            ->active()
            ->orderBy('sort')
            ->each(function (Service $service) use ($urls) {
                $urls->push($this->urlNode(
                    route('services.show', $service->slug),
                    '0.7',
                    'monthly',
                    $service->updated_at,
                ));
            });

        // Blog posts (published)
        Blog::query()
            ->published()
            ->orderByDesc('published_at')
            ->each(function (Blog $blog) use ($urls) {
                $urls->push($this->urlNode(
                    route('blog.show', $blog->slug),
                    '0.6',
                    'monthly',
                    $blog->published_at,
                ));
            });

        // Static CMS pages (footer + main pages)
        Page::query()
            ->published()
            ->orderBy('sort')
            ->each(function (Page $page) use ($urls) {
                $urls->push($this->urlNode(
                    route('pages.show', $page->slug),
                    '0.5',
                    'monthly',
                    $page->updated_at,
                ));
            });

        return $this->buildXml($urls->all());
    }

    protected function urlNode(string $loc, string $priority = '0.5', string $changefreq = 'monthly', $lastmod = null, array $extras = []): array
    {
        $node = [
            'loc' => $loc,
            'priority' => $priority,
            'changefreq' => $changefreq,
        ];
        if ($lastmod) {
            $node['lastmod'] = $lastmod instanceof \Carbon\Carbon ? $lastmod->toAtomString() : (string) $lastmod;
        }
        if (! empty($extras['product'])) {
            $node['product'] = $extras['product'];
        }
        return $node;
    }

    protected function buildXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $xml .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
        $xml .= ' xmlns:product="http://www.google.com/schemas/sitemap-product/1.0">';
        $xml .= "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($url['loc'])."</loc>\n";
            if (! empty($url['lastmod'])) {
                $xml .= '    <lastmod>'.$url['lastmod']."</lastmod>\n";
            }
            $xml .= '    <changefreq>'.$url['changefreq']."</changefreq>\n";
            $xml .= '    <priority>'.$url['priority']."</priority>\n";

            if (! empty($url['product'])) {
                $xml .= "    <product:product>\n";
                $xml .= '      <product:price>'.htmlspecialchars((string) $url['product']['price'])."</product:price>\n";
                $xml .= '      <product:currency>'.htmlspecialchars($url['product']['currency'])."</product:currency>\n";
                $xml .= '      <product:availability>'.htmlspecialchars($url['product']['availability'])."</product:availability>\n";
                $xml .= "    </product:product>\n";
            }

            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';
        return $xml;
    }

    /**
     * Clear sitemap cache (called when products/blogs/pages di-update).
     */
    public static function clearCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget('sitemap:xml:v1');
    }
}