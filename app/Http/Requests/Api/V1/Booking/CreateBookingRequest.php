<?php

namespace App\Http\Requests\Api\V1\Booking;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'technician_id' => ['nullable', 'integer', 'exists:users,id'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[1-9][0-9]{6,10}$/'],
            'customer_email' => ['nullable', 'email', 'max:191'],
            'location_type' => ['required', 'string', 'in:on_site,in_store,remote'],
            'location_address' => ['required_if:location_type,on_site', 'nullable', 'string', 'max:500'],
            'location_coordinates' => ['nullable', 'array'],
            'location_coordinates.lat' => ['required_with:location_coordinates', 'numeric', 'between:-90,90'],
            'location_coordinates.lng' => ['required_with:location_coordinates', 'numeric', 'between:-180,180'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_id.required' => 'Layanan wajib dipilih.',
            'scheduled_at.required' => 'Jadwal booking wajib diisi.',
            'scheduled_at.after' => 'Jadwal harus di waktu yang akan datang.',
            'customer_name.required' => 'Nama customer wajib diisi.',
            'customer_phone.regex' => 'Nomor WhatsApp tidak valid.',
            'location_type.required' => 'Tipe lokasi wajib dipilih.',
        ];
    }
}