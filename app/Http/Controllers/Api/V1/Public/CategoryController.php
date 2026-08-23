<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query()->active()->orderBy('sort')->orderBy('name');

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        } elseif ($request->boolean('roots_only')) {
            $query->roots();
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $categories = $query->paginate($perPage);

        return $this->success($categories, 'Categories retrieved successfully');
    }

    public function tree()
    {
        $categories = Category::active()
            ->roots()
            ->with(['children' => fn ($q) => $q->active()->orderBy('sort')])
            ->orderBy('sort')
            ->get();

        return $this->success($categories, 'Category tree retrieved');
    }

    public function show(string $slug)
    {
        $category = Category::where('slug', $slug)
            ->active()
            ->with(['children' => fn ($q) => $q->active()])
            ->first();

        if (! $category) {
            return $this->notFound('Category not found');
        }

        return $this->success($category, 'Category retrieved successfully');
    }
}