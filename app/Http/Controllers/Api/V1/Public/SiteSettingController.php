<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SiteSetting::query()->public()->orderBy('group')->orderBy('sort');

        if ($request->filled('group')) {
            $query->group($request->group);
        }

        $settings = $query->get();

        $grouped = $settings->groupBy('group')->map(function ($items, $group) {
            return $items->mapWithKeys(fn ($item) => [$item->key => $item->casted_value])
                ->toArray();
        });

        $flat = $settings->mapWithKeys(fn ($item) => [$item->key => $item->casted_value]);

        return $this->success([
            'settings' => $flat,
            'by_group' => $grouped,
        ], 'Pengaturan situs.');
    }
}