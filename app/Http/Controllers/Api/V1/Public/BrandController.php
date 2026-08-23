<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\Controller;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Brand::query()
            ->active()
            ->withCount(['products' => fn ($q) => $q->published()])
            ->orderBy('sort')
            ->orderBy('name');

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        if ($request->boolean('with_products')) {
            $query->with(['products' => fn ($q) => $q->published()->limit(4)]);
        }

        if ($request->boolean('paginated')) {
            $perPage = min((int) $request->input('per_page', 30), 100);

            return $this->success($query->paginate($perPage), 'Daftar brand.');
        }

        return $this->success($query->get(), 'Daftar brand.');
    }

    public function show(string $slug): JsonResponse
    {
        $brand = Brand::where('slug', $slug)
            ->active()
            ->withCount(['products' => fn ($q) => $q->published()])
            ->first();

        if (! $brand) {
            return $this->notFound('Brand tidak ditemukan.');
        }

        $products = $brand->products()
            ->published()
            ->with(['category', 'images'])
            ->latest('published_at')
            ->paginate(min((int) request('per_page', 12), 60));

        return $this->success([
            'brand' => $brand,
            'products' => $products,
        ], 'Detail brand.');
    }
}