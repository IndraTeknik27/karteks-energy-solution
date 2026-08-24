<?php

namespace App\Services\Shipping;

/**
 * Single shipping rate option (one courier + service combination).
 */
class ShippingRate
{
    public function __construct(
        public string $courierCode,        // 'jne', 'pos', 'sicepat', dll
        public string $courierName,        // 'JNE', 'POS Indonesia', dll
        public string $service,           // 'REG', 'YES', 'OKE', 'EZ', dll
        public string $serviceName,       // 'Reguler', 'Yes (1 hari)', dll
        public float $cost,               // Ongkir dalam IDR
        public int $etdDays,              // Estimated time delivery dalam hari
        public ?float $codCost = null,    // Biaya COD jika applicable
    ) {}

    public function toArray(): array
    {
        return [
            'courier_code' => $this->courierCode,
            'courier_name' => $this->courierName,
            'service' => $this->service,
            'service_name' => $this->serviceName,
            'cost' => $this->cost,
            'etd_days' => $this->etdDays,
            'cod_cost' => $this->codCost,
            'total' => $this->cost + (float) ($this->codCost ?? 0),
        ];
    }
}