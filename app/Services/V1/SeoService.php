<?php

namespace App\Services\V1;

use App\Models\Blog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Product;
use App\Models\Service;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Model;

class SeoService
{
    /**
     * Generate meta tags array untuk Blade consumption.
     * Keys: title, description, keywords, canonical, image, og_type, twitter_card.
     */
    public function generateMeta(?Model $model = null, array $overrides = []): array
    {
        $defaults = [
            'title' => $this->siteName(),
            'description' => $this->siteDescription(),
            'keywords' => $this->siteKeywords(),
            'canonical' => url()->current(),
            'image' => $this->ogImageDefault(),
            'og_type' => 'website',
            'twitter_card' => 'summary_large_image',
            'site_name' => $this->siteName(),
            'locale' => 'id_ID',
        ];

        $meta = $model ? array_merge($defaults, $this->fromModel($model)) : $defaults;

        // Tambah title suffix kalau belum ada
        if (! str_contains($meta['title'], $this->siteName())) {
            $suffix = config('karteks.seo.default_title_suffix', ' | KARTEKS ENERGY SOLUTION');
            // Jangan double-suffix kalau title sudah ada pipe + brand
            if (! preg_match('/[|]\s*'.preg_quote($this->siteName(), '/').'\s*$/', $meta['title'])) {
                $meta['title'] = $meta['title'] . $suffix;
            }
        }

        return array_merge($meta, $overrides);
    }

    /**
     * Extract meta fields dari berbagai model types.
     */
    protected function fromModel(Model $model): array
    {
        return match (true) {
            $model instanceof Product => $this->fromProduct($model),
            $model instanceof Blog => $this->fromBlog($model),
            $model instanceof Page => $this->fromPage($model),
            $model instanceof Category => $this->fromCategory($model),
            $model instanceof Brand => $this->fromBrand($model),
            $model instanceof Service => $this->fromService($model),
            default => [],
        };
    }

    protected function fromProduct(Product $product): array
    {
        $image = $product->featured_image_url ?? $this->ogImageDefault();
        $price = (float) ($product->sale_price ?: $product->price);
        $description = $product->meta_description
            ?: ($product->short_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 160));

        return [
            'title' => $product->meta_title ?: $product->name,
            'description' => $description,
            'keywords' => is_array($product->meta_keywords) ? implode(', ', $product->meta_keywords) : null,
            'image' => $image,
            'og_type' => 'product',
            'product:price:amount' => $price,
            'product:price:currency' => config('karteks.locale.currency', 'IDR'),
            'product:availability' => $product->manage_stock && $product->stock_qty <= 0 ? 'out of stock' : 'in stock',
        ];
    }

    protected function fromBlog(Blog $blog): array
    {
        return [
            'title' => $blog->meta_title ?: $blog->title,
            'description' => $blog->meta_description ?: $blog->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $blog->content), 160),
            'keywords' => $blog->tags->pluck('name')->implode(', ') ?: null,
            'image' => $blog->featured_image_url ?: $this->ogImageDefault(),
            'og_type' => 'article',
            'article:published_time' => $blog->published_at?->toIso8601String(),
            'article:author' => $blog->author?->name,
            'article:section' => $blog->category?->name,
        ];
    }

    protected function fromPage(Page $page): array
    {
        return [
            'title' => $page->meta_title ?: $page->title,
            'description' => $page->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $page->content), 160),
            'image' => $page->featured_image_url ?: $this->ogImageDefault(),
            'og_type' => 'article',
        ];
    }

    protected function fromCategory(Category $category): array
    {
        return [
            'title' => $category->meta_title ?: $category->name,
            'description' => $category->meta_description ?: $category->description ?: "Jelajahi koleksi produk {$category->name} dari KARTEKS ENERGY SOLUTION.",
            'image' => $category->image_url ?: $this->ogImageDefault(),
            'og_type' => 'website',
        ];
    }

    protected function fromBrand(Brand $brand): array
    {
        return [
            'title' => $brand->name,
            'description' => $brand->description ?: "Produk {$brand->name} original dari KARTEKS ENERGY SOLUTION.",
            'image' => $brand->logo_url ?: $this->ogImageDefault(),
            'og_type' => 'website',
        ];
    }

    protected function fromService(Service $service): array
    {
        return [
            'title' => $service->meta_title ?: $service->name,
            'description' => $service->meta_description ?: $service->short_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $service->description), 160),
            'image' => $service->image_url ?: $this->ogImageDefault(),
            'og_type' => 'service',
        ];
    }

    /**
     * JSON-LD: Organization / LocalBusiness (situs-wide).
     */
    public function organizationJsonLd(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => config('karteks.seo.schema_type', 'LocalBusiness'),
            '@id' => url('/#organization'),
            'name' => $this->siteName(),
            'legalName' => config('karteks.company.legal_name'),
            'url' => url('/'),
            'logo' => config('karteks.company.logo') ?: $this->ogImageDefault(),
            'description' => $this->siteDescription(),
            'telephone' => config('karteks.company.phone'),
            'email' => config('karteks.company.email'),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => config('karteks.company.address'),
                'addressRegion' => config('karteks.company.province'),
                'addressLocality' => config('karteks.company.city'),
                'addressCountry' => config('karteks.company.country', 'ID'),
            ],
            'sameAs' => array_values(array_filter([
                SiteSetting::get('social_facebook'),
                SiteSetting::get('social_instagram'),
                SiteSetting::get('social_youtube'),
                SiteSetting::get('social_tiktok'),
            ])),
        ];
    }

    /**
     * JSON-LD: Product untuk product detail page.
     */
    public function productJsonLd(Product $product): array
    {
        $image = $product->featured_image_url;
        $price = (float) ($product->sale_price ?: $product->price);
        $availability = $product->manage_stock && $product->stock_qty <= 0
            ? 'https://schema.org/OutOfStock'
            : 'https://schema.org/InStock';

        $reviews = $product->reviews()->where('is_approved', true)->get();
        $aggregateRating = null;
        if ($reviews->isNotEmpty()) {
            $aggregateRating = [
                '@type' => 'AggregateRating',
                'ratingValue' => round((float) $reviews->avg('rating'), 1),
                'reviewCount' => $reviews->count(),
                'bestRating' => 5,
                'worstRating' => 1,
            ];
        }

        $productUrl = route('catalog.show', $product->slug, true);

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            '@id' => $productUrl . '#product',
            'name' => $product->name,
            'description' => \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 500),
            'sku' => $product->sku,
            'image' => $image,
            'url' => $productUrl,
            'brand' => $product->brand ? [
                '@type' => 'Brand',
                'name' => $product->brand->name,
            ] : null,
            'offers' => [
                '@type' => 'Offer',
                'price' => $price,
                'priceCurrency' => config('karteks.locale.currency', 'IDR'),
                'availability' => $availability,
                'url' => $productUrl,
                'seller' => [
                    '@type' => 'Organization',
                    'name' => $this->siteName(),
                ],
            ],
        ];

        if ($aggregateRating) {
            $schema['aggregateRating'] = $aggregateRating;
        }

        return array_filter($schema, fn ($v) => $v !== null);
    }

    /**
     * JSON-LD: Article untuk blog post.
     */
    public function articleJsonLd(Blog $blog): array
    {
        $blogUrl = route('blog.show', $blog->slug, true);

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            '@id' => $blogUrl . '#article',
            'headline' => $blog->title,
            'description' => $blog->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $blog->content), 160),
            'image' => $blog->featured_image_url,
            'url' => $blogUrl,
            'datePublished' => $blog->published_at?->toIso8601String(),
            'dateModified' => $blog->updated_at?->toIso8601String(),
            'author' => $blog->author ? [
                '@type' => 'Person',
                'name' => $blog->author->name,
            ] : null,
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->siteName(),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => config('karteks.company.logo') ?: $this->ogImageDefault(),
                ],
            ],
            'articleSection' => $blog->category?->name,
            'keywords' => $blog->tags->pluck('name')->implode(', ') ?: null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /**
     * JSON-LD: BreadcrumbList dari array of [name, url].
     */
    public function breadcrumbJsonLd(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn ($item, $idx) => [
                '@type' => 'ListItem',
                'position' => $idx + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ], $items, array_keys($items)),
        ];
    }

    /**
     * JSON-LD: FAQPage dari FAQ collection.
     */
    public function faqJsonLd($faqs): array
    {
        if ($faqs instanceof \Illuminate\Support\Collection) {
            $faqs = $faqs->all();
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (Faq $faq) => [
                '@type' => 'Question',
                'name' => $faq->question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strip_tags((string) $faq->answer),
                ],
            ], $faqs),
        ];
    }

    /**
     * JSON-LD: Service untuk service detail page.
     */
    public function serviceJsonLd(Service $service): array
    {
        $serviceUrl = route('services.show', $service->slug, true);
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            '@id' => $serviceUrl . '#service',
            'name' => $service->name,
            'description' => $service->short_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $service->description), 500),
            'url' => $serviceUrl,
            'provider' => [
                '@type' => 'Organization',
                'name' => $this->siteName(),
            ],
            'areaServed' => [
                '@type' => 'Country',
                'name' => 'Indonesia',
            ],
        ];

        if ($service->base_price) {
            $data['offers'] = [
                '@type' => 'Offer',
                'price' => (float) $service->base_price,
                'priceCurrency' => config('karteks.locale.currency', 'IDR'),
            ];
        } elseif ($service->starting_price) {
            $data['offers'] = [
                '@type' => 'Offer',
                'price' => (float) $service->starting_price,
                'priceCurrency' => config('karteks.locale.currency', 'IDR'),
                'priceSpecification' => [
                    '@type' => 'PriceSpecification',
                    'priceCurrency' => config('karteks.locale.currency', 'IDR'),
                    'description' => 'Harga mulai dari',
                ],
            ];
        }

        return $data;
    }

    /**
     * JSON-LD: WebSite dengan SearchAction untuk sitelinks search box.
     */
    public function websiteJsonLd(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => url('/#website'),
            'url' => url('/'),
            'name' => $this->siteName(),
            'description' => $this->siteDescription(),
            'inLanguage' => 'id-ID',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => route('catalog.index') . '?search={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    // ---- Helpers ----

    protected function siteName(): string
    {
        return config('karteks.seo.site_name', config('karteks.company.name'));
    }

    protected function siteDescription(): string
    {
        return (string) config('karteks.seo.default_description', config('karteks.company.tagline'));
    }

    protected function siteKeywords(): string
    {
        return (string) config('karteks.seo.default_keywords', '');
    }

    protected function ogImageDefault(): ?string
    {
        $image = config('karteks.seo.og_image_default');
        if ($image) {
            return str_starts_with($image, 'http') ? $image : asset($image);
        }
        // Fallback ke logo company atau gambar statis
        $logo = config('karteks.company.logo');
        if ($logo) {
            return str_starts_with($logo, 'http') ? $logo : asset($logo);
        }
        return null;
    }

    public function twitterHandle(): ?string
    {
        return config('karteks.seo.twitter_handle');
    }
}