<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Services\V1\SeoService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(
        protected SeoService $seo,
    ) {}

    public function index(Request $request)
    {
        $query = Blog::query()
            ->published()
            ->with(['category', 'author'])
            ->latest('published_at');

        if ($request->filled('category_slug')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category_slug));
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('slug', $request->tag));
        }

        $blogs = $query->paginate(9)->withQueryString();

        $categories = BlogCategory::query()
            ->where('is_active', true)
            ->withCount(['blogs' => fn ($q) => $q->published()])
            ->orderBy('sort')
            ->get();

        $seoMeta = $this->seo->generateMeta(null, [
            'title' => 'Blog & Tips - KARTEKS ENERGY SOLUTION',
            'description' => 'Tips, panduan, dan berita terbaru seputar energi terbarukan, kendaraan listrik, dan teknologi baterai dari KARTEKS.',
            'canonical' => route('blog.index'),
        ]);
        $seoSchemas = [
            $this->seo->breadcrumbJsonLd([
                ['name' => 'Beranda', 'url' => route('home')],
                ['name' => 'Blog', 'url' => route('blog.index')],
            ]),
        ];

        return view('pages.blog.index', compact('blogs', 'categories', 'seoMeta', 'seoSchemas'));
    }

    public function show(string $slug)
    {
        $blog = Blog::query()
            ->where('slug', $slug)
            ->published()
            ->with(['category', 'author', 'tags'])
            ->first();

        if (! $blog) {
            abort(404, 'Artikel tidak ditemukan.');
        }

        Blog::where('id', $blog->id)->update(['views' => $blog->views + 1]);

        $related = Blog::query()
            ->published()
            ->where('id', '!=', $blog->id)
            ->when($blog->blog_category_id, fn ($q) => $q->where('blog_category_id', $blog->blog_category_id))
            ->latest('published_at')
            ->limit(3)
            ->get();

        $seoMeta = $this->seo->generateMeta($blog);
        $seoSchemas = [$this->seo->articleJsonLd($blog)];

        // Breadcrumb JSON-LD
        $breadcrumbItems = [
            ['name' => 'Beranda', 'url' => route('home')],
            ['name' => 'Blog', 'url' => route('blog.index')],
        ];
        if ($blog->category) {
            $breadcrumbItems[] = ['name' => $blog->category->name, 'url' => route('blog.index', ['category_slug' => $blog->category->slug])];
        }
        $breadcrumbItems[] = ['name' => $blog->title, 'url' => route('blog.show', $blog->slug)];
        $seoSchemas[] = $this->seo->breadcrumbJsonLd($breadcrumbItems);

        return view('pages.blog.show', compact('blog', 'related', 'seoMeta', 'seoSchemas'));
    }
}