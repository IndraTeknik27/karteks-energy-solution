<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\Controller;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Testimonial::query()
            ->active()
            ->orderByDesc('is_featured')
            ->orderBy('sort')
            ->orderByDesc('created_at');

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $limit = min((int) $request->input('limit', 12), 50);
        $testimonials = $query->limit($limit)->get();

        return $this->success($testimonials, 'Daftar testimonial.');
    }
}