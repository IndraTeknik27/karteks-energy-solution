<?php

namespace App\Filament\Admin\Resources\Blogs\Pages;

use App\Filament\Admin\Resources\Blogs\BlogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBlog extends EditRecord
{
    protected static string $resource = BlogResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Auto-set published_at saat toggle publish true
        if (! empty($data['is_published']) && empty($this->record->published_at)) {
            $data['published_at'] = now();
        }
        return $data;
    }
}