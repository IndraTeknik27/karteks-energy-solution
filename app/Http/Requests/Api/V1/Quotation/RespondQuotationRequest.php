<?php

namespace App\Http\Requests\Api\V1\Quotation;

use Illuminate\Foundation\Http\FormRequest;

class RespondQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}