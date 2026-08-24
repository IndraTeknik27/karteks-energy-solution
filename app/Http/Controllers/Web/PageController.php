<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\V1\SeoService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(
        protected SeoService $seo,
    ) {}

    public function show(Request $request, string $slug)
    {
        $page = Page::where('slug', $slug)->where('is_published', true)->first();

        if (! $page) {
            abort(404, 'Halaman tidak ditemukan.');
        }

        $seoMeta = $this->seo->generateMeta($page);
        $seoSchemas = [
            $this->seo->breadcrumbJsonLd([
                ['name' => 'Beranda', 'url' => route('home')],
                ['name' => $page->title, 'url' => route('pages.show', $page->slug)],
            ]),
        ];

        return view('pages.page', compact('page', 'seoMeta', 'seoSchemas'));
    }
}