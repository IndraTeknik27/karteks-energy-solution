<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\Controller;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Faq::query()->active();

        $faqs = $query->get();

        $grouped = $faqs->groupBy('category')->map(function ($items, $category) {
            return [
                'category' => $category ?: 'Umum',
                'count' => $items->count(),
                'faqs' => $items->values(),
            ];
        })->values();

        return $this->success([
            'faqs' => $faqs,
            'by_category' => $grouped,
        ], 'Daftar FAQ.');
    }
}