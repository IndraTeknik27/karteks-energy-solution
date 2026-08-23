<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CustomBatteryRequestFileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $url = null;
        if (Storage::disk('public')->exists($this->file_path)) {
            $url = Storage::disk('public')->url($this->file_path);
        }

        return [
            'id' => $this->id,
            'request_id' => $this->request_id,
            'file_path' => $this->file_path,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'file_size' => (int) $this->file_size,
            'size_kb' => (float) $this->size_kb,
            'size_human' => $this->humanFileSize(),
            'url' => $url,
            'uploaded_by' => $this->uploaded_by,
            'uploaded_at' => $this->created_at?->toIso8601String(),
        ];
    }

    protected function humanFileSize(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }
}