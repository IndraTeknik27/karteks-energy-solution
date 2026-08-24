<?php

namespace App\Services\Shipping;

class ShippingQuote
{
    /**
     * @param  array  $rates  Array of ShippingRate
     * @param  string|null  $error  Error message jika provider failed
     */
    public function __construct(
        public string $providerCode,
        public string $providerName,
        public string $originCity,
        public string $destinationCity,
        public int $weight,
        public array $rates = [],
        public ?string $error = null,
    ) {}

    public function toArray(): array
    {
        $ratesArr = array_map(fn ($r) => $r instanceof ShippingRate ? $r->toArray() : $r, $this->rates);

        return [
            'provider' => [
                'code' => $this->providerCode,
                'name' => $this->providerName,
            ],
            'origin' => $this->originCity,
            'destination' => $this->destinationCity,
            'weight' => $this->weight,
            'rates' => $ratesArr,
            'error' => $this->error,
        ];
    }

    public function isEmpty(): bool
    {
        return empty($this->rates);
    }
}