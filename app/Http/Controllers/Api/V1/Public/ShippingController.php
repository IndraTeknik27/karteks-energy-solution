<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\Controller;
use App\Models\SiteSetting;
use App\Services\Shipping\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function index(): JsonResponse
    {
        $config = SiteSetting::public()
            ->where('group', 'shipping')
            ->get()
            ->mapWithKeys(fn ($s) => [$s->key => $s->casted_value])
            ->toArray();

        return $this->success([
            'config' => $config,
            'options' => [
                ['code' => 'jne', 'name' => 'JNE', 'services' => ['REG', 'YES', 'OKE']],
                ['code' => 'pos', 'name' => 'POS Indonesia', 'services' => ['Paket Kilat', 'Express']],
                ['code' => 'tiki', 'name' => 'TIKI', 'services' => ['REG', 'ONS', 'ECO']],
                ['code' => 'sicepat', 'name' => 'SiCepat', 'services' => ['REG', 'BEST']],
                ['code' => 'jnt', 'name' => 'J&T Express', 'services' => ['EZ']],
            ],
            'default_courier' => $config['default_courier'] ?? 'jne',
            'free_shipping_threshold' => isset($config['free_shipping_threshold'])
                ? (float) $config['free_shipping_threshold']
                : null,
            'provider' => config('karteks.shipping.provider', 'manual'),
        ], 'Konfigurasi pengiriman.');
    }

    /**
     * Calculate live shipping rates based on origin + destination + weight.
     *
     * FASE 4.7: Delegates ke ShippingService (uses active provider dari config).
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'origin_city' => ['nullable', 'string', 'max:100'],
            'destination_city' => ['required', 'string', 'max:100'],
            'weight' => ['nullable', 'integer', 'min:100', 'max:50000'], // grams
            'courier' => ['nullable', 'string', 'max:50'],
        ]);

        $originCity = $validated['origin_city'] ?? config('karteks.shipping.default_origin_city', 'Gowa');
        $weight = $validated['weight'] ?? 1000;

        $shippingService = app(ShippingService::class);
        $quote = $shippingService->calculate($originCity, $validated['destination_city'], $weight);

        // Filter by courier if specified
        $rates = $quote->rates;
        if (! empty($validated['courier'])) {
            $rates = array_values(array_filter($rates, fn ($r) => $r->courierCode === $validated['courier']));
        }

        return $this->success([
            'origin' => $quote->originCity,
            'destination' => $quote->destinationCity,
            'weight' => $quote->weight,
            'provider' => [
                'code' => $quote->providerCode,
                'name' => $quote->providerName,
            ],
            'rates' => array_map(fn ($r) => $r->toArray(), $rates),
            'error' => $quote->error,
        ], 'Hasil kalkulasi ongkir.');
    }
}