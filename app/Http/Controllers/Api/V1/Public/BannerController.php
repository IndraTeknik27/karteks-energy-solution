<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\Controller;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Banner::query()
            ->active()
            ->orderBy('sort')
            ->orderByDesc('created_at');

        if ($request->filled('position')) {
            $query->position($request->position);
        }

        $banners = $query->get();

        $grouped = $banners->groupBy('position');

        return $this->success([
            'banners' => $banners,
            'by_position' => $grouped,
        ], 'Daftar banner aktif.');
    }
}