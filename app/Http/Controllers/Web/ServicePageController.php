<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Services\V1\SeoService;
use Illuminate\Http\Request;

class ServicePageController extends Controller
{
    public function __construct(
        protected SeoService $seo,
    ) {}

    public function index(Request $request)
    {
        $query = Service::query()->active()->with('category')->orderBy('sort')->orderBy('name');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('category_slug')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category_slug));
        }

        $services = $query->get();

        $categories = Category::active()
            ->whereHas('services', fn ($q) => $q->active())
            ->withCount(['services' => fn ($q) => $q->active()])
            ->orderBy('sort')
            ->get();

        $seoMeta = $this->seo->generateMeta(null, [
            'title' => 'Layanan & Jasa - KARTEKS ENERGY SOLUTION',
            'description' => 'Layanan profesional KARTEKS: konsultasi EV, custom battery, instalasi solar, maintenance, dan lainnya.',
            'canonical' => route('services.index'),
        ]);
        $seoSchemas = [
            $this->seo->breadcrumbJsonLd([
                ['name' => 'Beranda', 'url' => route('home')],
                ['name' => 'Layanan', 'url' => route('services.index')],
            ]),
        ];

        return view('pages.services.index', compact('services', 'categories', 'seoMeta', 'seoSchemas'));
    }

    public function show(string $slug)
    {
        $service = Service::query()
            ->where('slug', $slug)
            ->active()
            ->with('category')
            ->first();

        if (! $service) {
            abort(404, 'Layanan tidak ditemukan.');
        }

        $related = Service::query()
            ->active()
            ->where('id', '!=', $service->id)
            ->when($service->category_id, fn ($q) => $q->where('category_id', $service->category_id))
            ->limit(4)
            ->get();

        $seoMeta = $this->seo->generateMeta($service);
        $seoSchemas = [
            $this->seo->serviceJsonLd($service),
            $this->seo->breadcrumbJsonLd([
                ['name' => 'Beranda', 'url' => route('home')],
                ['name' => 'Layanan', 'url' => route('services.index')],
                ['name' => $service->name, 'url' => route('services.show', $service->slug)],
            ]),
        ];

        return view('pages.services.show', compact('service', 'related', 'seoMeta', 'seoSchemas'));
    }
}