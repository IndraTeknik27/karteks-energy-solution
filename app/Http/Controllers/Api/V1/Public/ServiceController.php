<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\Controller;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Service::query()
            ->active()
            ->with('category')
            ->orderBy('sort')
            ->orderBy('name');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('category_slug')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category_slug));
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $services = $query->get();

        return $this->success($services, 'Daftar layanan.');
    }

    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->active()
            ->whereHas('services', fn ($q) => $q->active())
            ->withCount(['services' => fn ($q) => $q->active()])
            ->orderBy('sort')
            ->orderBy('name')
            ->get();

        return $this->success($categories, 'Kategori layanan.');
    }

    public function show(string $slug): JsonResponse
    {
        $service = Service::where('slug', $slug)
            ->active()
            ->with('category')
            ->first();

        if (! $service) {
            return $this->notFound('Layanan tidak ditemukan.');
        }

        $related = Service::active()
            ->where('id', '!=', $service->id)
            ->when(
                $service->category_id,
                fn ($q) => $q->where('category_id', $service->category_id),
            )
            ->orderBy('sort')
            ->limit(4)
            ->get();

        return $this->success([
            'service' => $service,
            'related' => $related,
        ], 'Detail layanan.');
    }
}