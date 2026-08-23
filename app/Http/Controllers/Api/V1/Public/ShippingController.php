<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

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
        ], 'Konfigurasi pengiriman.');
    }
}