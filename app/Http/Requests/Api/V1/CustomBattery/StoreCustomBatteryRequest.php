<?php

namespace App\Http\Requests\Api\V1\CustomBattery;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomBatteryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $chemList = implode(',', config('karteks.battery_options.chemistry', []));
        $voltList = implode(',', config('karteks.battery_options.voltage', []));
        $apps = array_keys(config('karteks.battery_options.applications', []));

        return [
            'chemistry' => ['required', 'string', "in:{$chemList}"],
            'voltage' => ['required', 'string', "in:{$voltList}"],
            'capacity' => ['nullable', 'string', 'max:50'],
            'kwh' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'application' => ['required', 'string', 'in:'.implode(',', $apps)],
            'current_load' => ['nullable', 'string', 'max:100'],
            'dimensions' => ['nullable', 'array'],
            'dimensions.length' => ['required_with:dimensions', 'numeric', 'min:0'],
            'dimensions.width' => ['required_with:dimensions', 'numeric', 'min:0'],
            'dimensions.height' => ['required_with:dimensions', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'deadline' => ['nullable', 'date', 'after:today'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'chemistry.required' => 'Tipe kimia baterai wajib dipilih.',
            'voltage.required' => 'Voltase wajib dipilih.',
            'application.required' => 'Aplikasi penggunaan wajib dipilih.',
            'quantity.required' => 'Jumlah produksi wajib diisi.',
            'quantity.min' => 'Jumlah minimal 1 unit.',
            'description.required' => 'Deskripsi kebutuhan wajib diisi (minimal 20 karakter).',
            'description.min' => 'Deskripsi minimal 20 karakter.',
            'deadline.after' => 'Deadline harus lebih dari hari ini.',
        ];
    }
}