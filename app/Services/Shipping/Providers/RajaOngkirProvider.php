<?php

namespace App\Services\Shipping\Providers;

use App\Services\Shipping\ShippingQuote;
use App\Services\Shipping\ShippingRate;
use App\Services\Shipping\ShippingServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * RajaOngkir integration stub.
 *
 * RajaOngkir (https://rajaongkir.com) provides Indonesian shipping cost API
 * dari multiple couriers (JNE, POS, TIKI, SiCepat, J&T, dll).
 *
 * API Tiers:
 * - Starter (free): cache-based, slower
 * - Basic: real-time, paid subscription
 *
 * Untuk development tanpa API key, akan return fallback rates similar ke manual.
 */
class RajaOngkirProvider implements ShippingServiceInterface
{
    public function getCode(): string
    {
        return 'rajaongkir';
    }

    public function getName(): string
    {
        return 'RajaOngkir';
    }

    public function getServices(): array
    {
        return [
            ['code' => 'jne', 'name' => 'JNE', 'services' => [
                ['code' => 'REG', 'name' => 'Reguler', 'etd_days' => '2-3'],
                ['code' => 'YES', 'name' => 'YES (Yakin Esok Sampai)', 'etd_days' => '1'],
                ['code' => 'OKE', 'name' => 'Ongkos Kirim Ekonomis', 'etd_days' => '3-5'],
            ]],
            ['code' => 'pos', 'name' => 'POS Indonesia', 'services' => [
                ['code' => 'Paket Kilat', 'name' => 'Paket Kilat Khusus', 'etd_days' => '2-3'],
                ['code' => 'Express', 'name' => 'Express Next Day', 'etd_days' => '1'],
            ]],
            ['code' => 'tiki', 'name' => 'TIKI', 'services' => [
                ['code' => 'REG', 'name' => 'Reguler Service', 'etd_days' => '2-3'],
                ['code' => 'ONS', 'name' => 'Over Night Service', 'etd_days' => '1'],
            ]],
            ['code' => 'sicepat', 'name' => 'SiCepat', 'services' => [
                ['code' => 'REG', 'name' => 'Reguler', 'etd_days' => '2-3'],
                ['code' => 'BEST', 'name' => 'Besok Sampai Tujuan', 'etd_days' => '1'],
            ]],
            ['code' => 'jnt', 'name' => 'J&T Express', 'services' => [
                ['code' => 'EZ', 'name' => 'Regular Service', 'etd_days' => '2-4'],
            ]],
        ];
    }

    public function calculate(
        string $originCity,
        string $destinationCity,
        int $weight = 1000,
        array $items = []
    ): ShippingQuote {
        $apiKey = config('karteks.shipping.rajaongkir.api_key');
        $package = config('karteks.shipping.rajaongkir.package', 'starter');

        // Fallback: jika tidak ada API key, return empty rates
        if (empty($apiKey)) {
            Log::debug('RajaOngkir: no API key, returning empty rates');
            return new ShippingQuote(
                providerCode: $this->getCode(),
                providerName: $this->getName(),
                originCity: $originCity,
                destinationCity: $destinationCity,
                weight: $weight,
                rates: [],
                error: 'RajaOngkir API key belum dikonfigurasi.',
            );
        }

        try {
            // RajaOngkir API: POST /calculate
            $response = Http::withHeaders([
                'key' => $apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->timeout(10)->post('https://api.rajaongkir.com/starter/cost', [
                'origin' => $this->resolveCityId($originCity),
                'destination' => $this->resolveCityId($destinationCity),
                'weight' => $weight,
                'courier' => 'jne:pos:tiki:sicepat:jnt',
            ]);

            if (! $response->successful()) {
                throw new \Exception('RajaOngkir API error: '.$response->status());
            }

            $data = $response->json();
            $rates = [];

            foreach ($data['rajaongkir']['results'] ?? [] as $courier) {
                foreach ($courier['costs'] ?? [] as $service) {
                    $rates[] = new ShippingRate(
                        courierCode: strtolower($courier['code']),
                        courierName: strtoupper($courier['code']),
                        service: $service['service'],
                        serviceName: $service['service'].' - '.$courier['name'],
                        cost: (float) $service['cost'][0]['value'],
                        etdDays: $this->parseEtdDays($service['cost'][0]['etd']),
                        codCost: null,
                    );
                }
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
            Log::warning('RajaOngkir API call failed: '.$e->getMessage());
            return new ShippingQuote(
                providerCode: $this->getCode(),
                providerName: $this->getName(),
                originCity: $originCity,
                destinationCity: $destinationCity,
                weight: $weight,
                rates: [],
                error: 'Gagal hit RajaOngkir: '.$e->getMessage(),
            );
        }
    }

    public function track(string $trackingNumber, string $courierCode): array
    {
        // RajaOngkir waybill API: POST /waybill
        return []; // Stub - implement with real API when key available
    }

    public function bookShipment(array $shipmentData): array
    {
        return [
            'tracking_number' => null,
            'courier_tracking_url' => null,
            'status' => 'pending',
            'raw_response' => ['error' => 'Booking requires RajaOngkir Pro/Business tier.'],
        ];
    }

    /**
     * Map city name to RajaOngkir city_id.
     * Production: pakai RajaOngkir /city endpoint + cache.
     */
    protected function resolveCityId(string $city): string
    {
        // Stub - real impl pakai cached city_id mapping
        return strtolower($city);
    }

    protected function parseEtdDays($etd): int
    {
        if (is_numeric($etd)) return (int) $etd;
        if (preg_match('/(\d+)-(\d+)/', $etd, $m)) return (int) $m[2];
        return 3;
    }
}