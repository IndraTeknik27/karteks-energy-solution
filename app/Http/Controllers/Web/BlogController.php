<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogController extends Controller
{
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

        return view('pages.blog.index', compact('blogs', 'categories'));
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

        return view('pages.blog.show', compact('blog', 'related'));
    }
}