<?php

namespace App\Http\Requests\Api\V1\CustomBattery;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:'.((int) config('karteks.upload.max_file_size_kb', 10240)),
                'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,zip,rar,dwg,dxf,step,iges',
            ],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File wajib dipilih.',
            'file.max' => 'Ukuran file terlalu besar (max '.((int) config('karteks.upload.max_file_size_kb', 10240) / 1024).' MB).',
            'file.mimes' => 'Format file tidak didukung. Gunakan: jpg, png, webp, pdf, doc, dwg, step, dll.',
        ];
    }
}