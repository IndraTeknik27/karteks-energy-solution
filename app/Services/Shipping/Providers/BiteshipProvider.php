<?php

namespace App\Services\Shipping\Providers;

use App\Services\Shipping\ShippingQuote;
use App\Services\Shipping\ShippingRate;
use App\Services\Shipping\ShippingServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Biteship integration stub.
 *
 * Biteship (https://biteship.com) - Indonesian shipping aggregator
 * dengan API lebih modern dari RajaOngkir (REST + JSON, webhook support).
 *
 * Free tier tersedia dengan rate limit.
 *
 * Production setup:
 * - Daftar di https://biteship.com
 * - Set Biteship API key di .env: BITESHIP_API_KEY=xxx
 * - Pilih courier yang aktif di dashboard
 */
class BiteshipProvider implements ShippingServiceInterface
{
    public function getCode(): string
    {
        return 'biteship';
    }

    public function getName(): string
    {
        return 'Biteship';
    }

    public function getServices(): array
    {
        return [
            ['code' => 'jne', 'name' => 'JNE', 'services' => [
                ['code' => 'reg', 'name' => 'Reguler', 'etd_days' => '2-3'],
                ['code' => 'yes', 'name' => 'YES (Yakin Esok Sampai)', 'etd_days' => '1'],
            ]],
            ['code' => 'sicepat', 'name' => 'SiCepat', 'services' => [
                ['code' => 'reg', 'name' => 'Reguler', 'etd_days' => '2-3'],
                ['code' => 'best', 'name' => 'Best (Besok Sampai)', 'etd_days' => '1'],
            ]],
            ['code' => 'jnt', 'name' => 'J&T Express', 'services' => [
                ['code' => 'ez', 'name' => 'Regular', 'etd_days' => '2-4'],
            ]],
            ['code' => 'anteraja', 'name' => 'AnterAja', 'services' => [
                ['code' => 'reg', 'name' => 'Reguler', 'etd_days' => '2-3'],
                ['code' => 'next', 'name' => 'Next Day', 'etd_days' => '1'],
            ]],
            ['code' => 'lion', 'name' => 'Lion Parcel', 'services' => [
                ['code' => 'reg', 'name' => 'Reguler', 'etd_days' => '2-4'],
            ]],
        ];
    }

    public function calculate(
        string $originCity,
        string $destinationCity,
        int $weight = 1000,
        array $items = []
    ): ShippingQuote {
        $apiKey = config('karteks.shipping.biteship.api_key');

        if (empty($apiKey)) {
            return new ShippingQuote(
                providerCode: $this->getCode(),
                providerName: $this->getName(),
                originCity: $originCity,
                destinationCity: $destinationCity,
                weight: $weight,
                rates: [],
                error: 'Biteship API key belum dikonfigurasi.',
            );
        }

        try {
            // Biteship v1 rates endpoint: POST /v1/rates/couriers
            $response = Http::withToken($apiKey)
                ->timeout(10)
                ->acceptJson()
                ->post('https://api.biteship.com/v1/rates/couriers', [
                    'origin_area_id' => $this->resolveAreaId($originCity),
                    'destination_area_id' => $this->resolveAreaId($destinationCity),
                    'couriers' => 'jne,sicepat,jnt,anteraja,lion',
                    'items' => [
                        [
                            'name' => 'Cart Items',
                            'description' => 'KARTEKS Order',
                            'value' => 100000,
                            'length' => 30,
                            'width' => 20,
                            'height' => 10,
                            'weight' => max(100, (int) ($weight / 1000) * 1000), // grams
                            'quantity' => 1,
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                throw new \Exception('Biteship API error: '.$response->status());
            }

            $data = $response->json();
            $rates = [];

            foreach ($data['pricing'] ?? [] as $rate) {
                $rates[] = new ShippingRate(
                    courierCode: $rate['courier_code'] ?? 'unknown',
                    courierName: strtoupper($rate['courier_name'] ?? $rate['courier_code'] ?? 'unknown'),
                    service: $rate['service_code'] ?? 'reg',
                    serviceName: $rate['service_name'] ?? $rate['courier_service'] ?? 'Reguler',
                    cost: (float) ($rate['price'] ?? 0),
                    etdDays: $this->parseEtdDays($rate['shipment_duration_range'] ?? '2-3'),
                    codCost: null,
                );
            }

            return new ShippingQuote(
                providerCode: $this->getCode(),
                providerName: $this->getName(),
                originCity: $originCity,
                destinationCity: $destinationCity,
                weight: $weight,
                rates: $rates,
            );
        } catch (\Throwable $e) {
            Log::warning('Biteship API call failed: '.$e->getMessage());
            return new ShippingQuote(
                providerCode: $this->getCode(),
                providerName: $this->getName(),
                originCity: $originCity,
                destinationCity: $destinationCity,
                weight: $weight,
                rates: [],
                error: 'Gagal hit Biteship: '.$e->getMessage(),
            );
        }
    }

    public function track(string $trackingNumber, string $courierCode): array
    {
        $apiKey = config('karteks.shipping.biteship.api_key');
        if (empty($apiKey)) {
            return [];
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(10)
                ->acceptJson()
                ->get("https://api.biteship.com/v1/trackings/{$trackingNumber}/couriers/{$courierCode}");

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();
            $history = [];

            foreach ($data['history'] ?? [] as $event) {
                $history[] = [
                    'status' => $event['status'] ?? 'unknown',
                    'location' => $event['location'] ?? '',
                    'description' => $event['note'] ?? $event['status'] ?? '',
                    'occurred_at' => $event['updated_at'] ?? null,
                ];
            }

            return $history;
        } catch (\Throwable $e) {
            Log::warning('Biteship tracking failed: '.$e->getMessage());
            return [];
        }
    }

    public function bookShipment(array $shipmentData): array
    {
        $apiKey = config('karteks.shipping.biteship.api_key');
        if (empty($apiKey)) {
            return [
                'tracking_number' => null,
                'courier_tracking_url' => null,
                'status' => 'pending',
                'raw_response' => ['error' => 'Biteship API key tidak ditemukan.'],
            ];
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(15)
                ->acceptJson()
                ->post('https://api.biteship.com/v1/orders', [
                    'shipper_contact_name' => $shipmentData['shipper_name'] ?? config('karteks.company.name'),
                    'shipper_contact_phone' => $shipmentData['shipper_phone'] ?? config('karteks.company.phone'),
                    'shipper_contact_email' => $shipmentData['shipper_email'] ?? config('karteks.company.email'),
                    'shipper_organization' => config('karteks.company.name'),
                    'origin_contact_name' => $shipmentData['shipper_name'] ?? config('karteks.company.name'),
                    'origin_address' => $shipmentData['origin_address'] ?? '',
                    'origin_area_id' => $this->resolveAreaId($shipmentData['origin_city'] ?? ''),
                    'origin_postal_code' => $shipmentData['origin_postal'] ?? '',
                    'destination_contact_name' => $shipmentData['receiver_name'] ?? '',
                    'destination_contact_phone' => $shipmentData['receiver_phone'] ?? '',
                    'destination_address' => $shipmentData['destination_address'] ?? '',
                    'destination_area_id' => $this->resolveAreaId($shipmentData['destination_city'] ?? ''),
                    'destination_postal_code' => $shipmentData['destination_postal'] ?? '',
                    'courier_company' => $shipmentData['courier_code'] ?? 'jne',
                    'courier_type' => $shipmentData['courier_service'] ?? 'reg',
                    'delivery_type' => 'now',
                    'items' => $shipmentData['items'] ?? [],
                ]);

            if (! $response->successful()) {
                throw new \Exception('Biteship booking failed: '.$response->status());
            }

            $data = $response->json();

            return [
                'tracking_number' => $data['courier']['waybill_id'] ?? $data['id'] ?? null,
                'courier_tracking_url' => $data['courier']['link'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'raw_response' => $data,
            ];
        } catch (\Throwable $e) {
            Log::warning('Biteship booking failed: '.$e->getMessage());
            return [
                'tracking_number' => null,
                'courier_tracking_url' => null,
                'status' => 'pending',
                'raw_response' => ['error' => $e->getMessage()],
            ];
        }
    }

    /**
     * Map city name to Biteship area_id.
     */
    protected function resolveAreaId(string $city): string
    {
        // Stub: Biteship requires area_id from their /v1/maps API
        // Implement with cache di production: cache area mapping 1 day
        return strtolower($city);
    }

    protected function parseEtdDays($etd): int
    {
        if (is_numeric($etd)) return (int) $etd;
        if (preg_match('/(\d+)-(\d+)/', $etd, $m)) return (int) $m[2];
        return 3;
    }
}