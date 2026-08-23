<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Page::query()->published()->orderBy('sort')->orderBy('title');

        if ($request->boolean('footer_only')) {
            $query->footer();
        }

        return $this->success($query->get(), 'Daftar halaman.');
    }

    public function show(string $slug): JsonResponse
    {
        $page = Page::where('slug', $slug)->published()->first();

        if (! $page) {
            return $this->notFound('Halaman tidak ditemukan.');
        }

        return $this->success($page, 'Detail halaman.');
    }
}