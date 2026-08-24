<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BannerClickController extends Controller
{
    /**
     * Track banner click. Fire-and-forget endpoint.
     * TIDAK return JSON agar bisa dipanggil via navigator.sendBeacon().
     */
    public function click(Request $request, Banner $banner)
    {
        try {
            $banner->recordClick();
        } catch (\Throwable $e) {
            Log::warning('Banner click tracking failed: '.$e->getMessage(), [
                'banner_id' => $banner->id,
            ]);
        }

        // Return 204 No Content (lightweight, fire-and-forget friendly)
        return response()->noContent();
    }
}