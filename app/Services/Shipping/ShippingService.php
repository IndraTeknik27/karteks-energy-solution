<?php

namespace App\Services\Shipping;

use App\Services\Shipping\Providers\BiteshipProvider;
use App\Services\Shipping\Providers\ManualShippingProvider;
use App\Services\Shipping\Providers\RajaOngkirProvider;
use Illuminate\Support\Facades\Log;

/**
 * Shipping orchestrator.
 *
 * Resolve active provider dari config dan delegate calculate/track/book ke provider.
 * Bisa fallback ke ManualProvider jika third-party API down.
 *
 * Method utama untuk Cart/Checkout:
 * - calculate() — return ShippingQuote dengan rates
 * - choose() — pick 1 specific rate by courier+service
 * - track() — get tracking history
 * - book() — book shipment dan return resi
 */
class ShippingService
{
    public const PROVIDER_MANUAL = 'manual';
    public const PROVIDER_RAJAONGKIR = 'rajaongkir';
    public const PROVIDER_BITESHIP = 'biteship';

    public const PROVIDERS = [
        self::PROVIDER_MANUAL,
        self::PROVIDER_RAJAONGKIR,
        self::PROVIDER_BITESHIP,
    ];

    protected ?ShippingServiceInterface $provider = null;

    /**
     * Get active provider dari config (cached per request).
     */
    public function getProvider(): ShippingServiceInterface
    {
        if ($this->provider !== null) {
            return $this->provider;
        }

        $code = config('karteks.shipping.provider', self::PROVIDER_MANUAL);
        return $this->provider = $this->resolveProvider($code);
    }

    /**
     * Get available providers yang aktif (have API key).
     */
    public function getAvailableProviders(): array
    {
        $available = [];
        foreach (self::PROVIDERS as $code) {
            try {
                $provider = $this->resolveProvider($code);
                $available[$code] = [
                    'code' => $provider->getCode(),
                    'name' => $provider->getName(),
                    'available' => true,
                ];
            } catch (\Throwable $e) {
                $available[$code] = [
                    'code' => $code,
                    'name' => $code,
                    'available' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }
        return $available;
    }

    /**
     * Calculate shipping via active provider, dengan fallback ke manual.
     */
    public function calculate(
        string $originCity,
        string $destinationCity,
        int $weight = 1000,
        array $items = []
    ): ShippingQuote {
        try {
            $quote = $this->getProvider()->calculate($originCity, $destinationCity, $weight, $items);

            // Fallback ke manual jika rates kosong (provider API down / no key)
            if ($quote->isEmpty() && ! $this->isManualProvider()) {
                Log::warning('Shipping provider '.$this->getProvider()->getCode().' returned empty, falling back to manual');
                $quote = app(ManualShippingProvider::class)
                    ->calculate($originCity, $destinationCity, $weight, $items);
            }

            return $quote;
        } catch (\Throwable $e) {
            Log::error('Shipping calculate failed: '.$e->getMessage());
            // Last resort: manual fallback
            return app(ManualShippingProvider::class)
                ->calculate($originCity, $destinationCity, $weight, $items);
        }
    }

    /**
     * Track shipment by tracking number.
     */
    public function track(string $trackingNumber, ?string $courierCode = null): array
    {
        return $this->getProvider()->track($trackingNumber, (string) $courierCode);
    }

    /**
     * Book shipment.
     */
    public function book(array $shipmentData): array
    {
        return $this->getProvider()->bookShipment($shipmentData);
    }

    /**
     * Pick specific rate from a quote result.
     */
    public function pickRate(ShippingQuote $quote, string $courierCode, string $service): ?ShippingRate
    {
        foreach ($quote->rates as $rate) {
            if ($rate instanceof ShippingRate
                && $rate->courierCode === $courierCode
                && $rate->service === $service) {
                return $rate;
            }
        }
        return null;
    }

    /**
     * Pick cheapest rate dari quote.
     */
    public function cheapestRate(ShippingQuote $quote): ?ShippingRate
    {
        $cheapest = null;
        foreach ($quote->rates as $rate) {
            if (! $rate instanceof ShippingRate) continue;
            if ($cheapest === null || $rate->cost < $cheapest->cost) {
                $cheapest = $rate;
            }
        }
        return $cheapest;
    }

    /**
     * Pick fastest rate (lowest ETD).
     */
    public function fastestRate(ShippingQuote $quote): ?ShippingRate
    {
        $fastest = null;
        foreach ($quote->rates as $rate) {
            if (! $rate instanceof ShippingRate) continue;
            if ($fastest === null || $rate->etdDays < $fastest->etdDays) {
                $fastest = $rate;
            }
        }
        return $fastest;
    }

    /**
     * Get ALL services from active provider (untuk dropdown).
     */
    public function getServices(): array
    {
        return $this->getProvider()->getServices();
    }

    public function getActiveProviderCode(): string
    {
        return $this->getProvider()->getCode();
    }

    public function isManualProvider(): bool
    {
        return $this->getProvider()->getCode() === self::PROVIDER_MANUAL;
    }

    /**
     * Resolve provider instance dari code.
     */
    protected function resolveProvider(string $code): ShippingServiceInterface
    {
        return match ($code) {
            self::PROVIDER_RAJAONGKIR => app(RajaOngkirProvider::class),
            self::PROVIDER_BITESHIP => app(BiteshipProvider::class),
            default => app(ManualShippingProvider::class),
        };
    }
}