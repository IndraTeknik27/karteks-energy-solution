<?php

namespace App\Filament\Admin\Resources\Blogs\Pages;

use App\Filament\Admin\Resources\Blogs\BlogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlog extends CreateRecord
{
    protected static string $resource = BlogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-set published_at jika publish true
        if (! empty($data['is_published']) && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        // Auto-set reading time dari content jika kosong
        if (empty($data['reading_time']) && ! empty($data['content'])) {
            $wordCount = str_word_count(strip_tags($data['content']));
            $data['reading_time'] = max(1, (int) ceil($wordCount / 200));
        }
        return $data;
    }
}