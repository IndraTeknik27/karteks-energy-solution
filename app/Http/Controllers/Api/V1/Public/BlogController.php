<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Blog::query()
            ->published()
            ->with(['category', 'author'])
            ->latest('published_at');

        if ($request->filled('category_id')) {
            $query->where('blog_category_id', $request->category_id);
        }

        if ($request->filled('category_slug')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category_slug));
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('slug', $request->tag));
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        if ($request->boolean('paginated')) {
            $perPage = min((int) $request->input('per_page', 9), 50);

            return $this->success($query->paginate($perPage), 'Daftar artikel.');
        }

        $blogs = $query->limit((int) $request->input('limit', 9))->get();

        return $this->success($blogs, 'Daftar artikel.');
    }

    public function categories(): JsonResponse
    {
        $categories = BlogCategory::query()
            ->where('is_active', true)
            ->withCount(['blogs' => fn ($q) => $q->published()])
            ->orderBy('sort')
            ->orderBy('name')
            ->get();

        return $this->success($categories, 'Kategori blog.');
    }

    public function tags(): JsonResponse
    {
        $tags = Tag::query()
            ->whereHas('blogs', fn ($q) => $q->published())
            ->withCount(['blogs' => fn ($q) => $q->published()])
            ->orderByDesc('blogs_count')
            ->limit(50)
            ->get();

        return $this->success($tags, 'Tag populer.');
    }

    public function show(string $slug): JsonResponse
    {
        $blog = Blog::where('slug', $slug)
            ->published()
            ->with(['category', 'author', 'tags'])
            ->first();

        if (! $blog) {
            return $this->notFound('Artikel tidak ditemukan.');
        }

        Blog::where('id', $blog->id)->update(['views' => $blog->views + 1]);

        $related = Blog::published()
            ->where('id', '!=', $blog->id)
            ->when(
                $blog->blog_category_id,
                fn ($q) => $q->where('blog_category_id', $blog->blog_category_id),
            )
            ->latest('published_at')
            ->limit(4)
            ->get();

        return $this->success([
            'blog' => $blog,
            'related' => $related,
        ], 'Detail artikel.');
    }
}