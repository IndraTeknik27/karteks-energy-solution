<?php

namespace App\Services\Shipping;

/**
 * Shipping service contract.
 *
 * Implementations: ManualShippingProvider (default), RajaOngkirProvider,
 * BiteshipProvider. Setiap provider menghitung ongkir dengan logic sendiri,
 * tapi harus return array of ShippingRate untuk konsistensi.
 */
interface ShippingServiceInterface
{
    /**
     * Get list of available shipping services (couriers + services).
     */
    public function getServices(): array;

    /**
     * Calculate shipping cost dari origin ke destination dengan weight + items.
     *
     * @param  string  $originCity  City name (e.g. 'Gowa')
     * @param  string  $destinationCity
     * @param  int  $weight  Total weight in grams
     * @param  array  $items  Optional items detail (untuk calculate volumetric)
     * @return ShippingQuote
     */
    public function calculate(
        string $originCity,
        string $destinationCity,
        int $weight = 1000,
        array $items = []
    ): ShippingQuote;

    /**
     * Track shipment by tracking number.
     * Returns list of tracking events with timestamp + status + location.
     */
    public function track(string $trackingNumber, string $courierCode): array;

    /**
     * Generate booking/resi number untuk courier.
     * Most providers return immediately; RajaOngkir/Biteship akan call API real.
     */
    public function bookShipment(array $shipmentData): array;

    /**
     * Provider code (e.g. 'manual', 'rajaongkir', 'biteship').
     */
    public function getCode(): string;

    /**
     * Provider name for display.
     */
    public function getName(): string;
}