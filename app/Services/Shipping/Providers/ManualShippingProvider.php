<?php

namespace App\Services\Shipping\Providers;

use App\Services\Shipping\ShippingQuote;
use App\Services\Shipping\ShippingRate;
use App\Services\Shipping\ShippingServiceInterface;
use Illuminate\Support\Facades\Log;

/**
 * Default manual shipping provider.
 *
 * Pakai flat-rate table dari config (karteks.shipping.manual).
 * Cocok untuk lokal pickup atau startup yang belum punya integrasi kurir API.
 *
 * Pricing strategy:
 * - Base cost per courier
 * - Per-kg surcharge
 * - Zone multiplier (same province vs different)
 */
class ManualShippingProvider implements ShippingServiceInterface
{
    public function getCode(): string
    {
        return 'manual';
    }

    public function getName(): string
    {
        return 'Manual (Flat Rate)';
    }

    public function getServices(): array
    {
        $config = config('karteks.shipping.manual', []);
        $couriers = $config['couriers'] ?? [];

        $result = [];
        foreach ($couriers as $code => $courier) {
            $services = [];
            foreach ($courier['services'] ?? [] as $key => $service) {
                $services[] = [
                    'code' => $key,
                    'name' => $service['name'] ?? strtoupper($key),
                    'etd_days' => $service['etd_days'] ?? '3-5',
                ];
            }
            $result[] = [
                'code' => $code,
                'name' => $courier['name'] ?? strtoupper($code),
                'services' => $services,
            ];
        }
        return $result;
    }

    public function calculate(
        string $originCity,
        string $destinationCity,
        int $weight = 1000,
        array $items = []
    ): ShippingQuote {
        try {
            $config = config('karteks.shipping.manual', []);
            $couriers = $config['couriers'] ?? [];

            if (empty($couriers)) {
                return new ShippingQuote(
                    providerCode: $this->getCode(),
                    providerName: $this->getName(),
                    originCity: $originCity,
                    destinationCity: $destinationCity,
                    weight: $weight,
                    rates: [],
                    error: 'Manual shipping belum dikonfigurasi.',
                );
            }

            // Determine zone (same province vs different)
            $zone = $this->determineZone($originCity, $destinationCity);
            $zoneMultiplier = config("karteks.shipping.zones.{$zone}.multiplier", 1.0);

            // Convert grams to kg (rounded up to 0.5 kg steps)
            $weightKg = max(0.5, ceil($weight / 500) / 2);

            $rates = [];
            foreach ($couriers as $code => $courier) {
                foreach ($courier['services'] ?? [] as $key => $service) {
                    $baseCost = (float) ($service['base_cost'] ?? 10000);
                    $perKgCost = (float) ($service['per_kg_cost'] ?? 5000);
                    $codSurcharge = (float) ($service['cod_cost'] ?? 0);

                    $cost = ($baseCost + ($weightKg * $perKgCost)) * $zoneMultiplier;

                    $rates[] = new ShippingRate(
                        courierCode: $code,
                        courierName: $courier['name'] ?? strtoupper($code),
                        service: $key,
                        serviceName: $service['name'] ?? strtoupper($key),
                        cost: round($cost, 2),
                        etdDays: $this->parseEtdDays($service['etd_days'] ?? '3-5', $zone),
                        codCost: $codSurcharge > 0 ? $codSurcharge : null,
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
            Log::error('ManualShippingProvider error: '.$e->getMessage());
            return new ShippingQuote(
                providerCode: $this->getCode(),
                providerName: $this->getName(),
                originCity: $originCity,
                destinationCity: $destinationCity,
                weight: $weight,
                rates: [],
                error: 'Gagal menghitung ongkir: '.$e->getMessage(),
            );
        }
    }

    public function track(string $trackingNumber, string $courierCode): array
    {
        // Manual provider tidak punya tracking real-time.
        // Return mock history.
        return [
            [
                'status' => 'Paket diterima di lokasi seller',
                'location' => 'Gowa, Sulawesi Selatan',
                'occurred_at' => now()->subDays(2)->toIso8601String(),
                'description' => 'Paket telah diterima dari seller untuk dikirim.',
            ],
            [
                'status' => 'Dalam perjalanan',
                'location' => 'Hub Gowa',
                'occurred_at' => now()->subDays(1)->toIso8601String(),
                'description' => 'Paket sedang dalam perjalanan menuju '.$this->getDestinationFromTracking($trackingNumber).'.',
            ],
        ];
    }

    public function bookShipment(array $shipmentData): array
    {
        // Generate local resi number
        $resi = strtoupper(substr($shipmentData['courier_code'] ?? 'MAN', 0, 3))
            .'-'.date('Ymd')
            .'-'.str_pad((string) ($shipmentData['id'] ?? random_int(1, 9999)), 4, '0', STR_PAD_LEFT);

        return [
            'tracking_number' => $resi,
            'courier_tracking_url' => url('/tracking/'.$resi),
            'status' => 'pending',
            'raw_response' => ['provider' => 'manual', 'auto_generated' => true],
        ];
    }

    /**
     * Determine shipping zone berdasarkan origin-destination city pair.
     */
    protected function determineZone(string $origin, string $destination): string
    {
        $origin = strtolower(trim($origin));
        $destination = strtolower(trim($destination));

        // Same island heuristic
        $sameIslandCities = [
            'sulawesi' => ['gowa', 'makassar', 'maros', 'bone', 'soppeng', 'takalar', 'jeneponto', 'bantaeng', 'bulukumba', 'pinrang'],
            'jawa' => ['jakarta', 'bandung', 'surabaya', 'semarang', 'yogyakarta', 'malang', 'bekasi', 'tangerang', 'depok', 'bogor'],
            'sumatera' => ['medan', 'padang', 'palembang', 'pekanbaru', 'jambi', 'bandar lampung', 'bengkulu', 'palembang'],
        ];

        foreach ($sameIslandCities as $island => $cities) {
            $inIsland = false;
            foreach ($cities as $city) {
                if (str_contains($origin, $city) || str_contains($destination, $city)) {
                    $inIsland = true;
                    break;
                }
            }
            if ($inIsland) {
                // Both in same island
                $originInIsland = false;
                $destInIsland = false;
                foreach ($cities as $city) {
                    if (str_contains($origin, $city)) $originInIsland = true;
                    if (str_contains($destination, $city)) $destInIsland = true;
                }
                if ($originInIsland && $destInIsland) {
                    return 'local'; // Same island
                }
            }
        }
        return 'national'; // Cross island
    }

    protected function parseEtdDays($etd, string $zone): int
    {
        if (is_numeric($etd)) {
            return (int) $etd;
        }
        // Parse "3-5" → take max
        if (preg_match('/(\d+)-(\d+)/', $etd, $m)) {
            return (int) $m[2];
        }
        // Adjust by zone
        return $zone === 'local' ? 3 : 5;
    }

    protected function getDestinationFromTracking(string $trackingNumber): string
    {
        // Heuristic: tracking format MAN-YYYYMMDD-XXXX
        return 'lokasi customer';
    }
}