<?php

namespace App\Filament\Admin\Resources\NewsletterSubscribers\Pages;

use App\Filament\Admin\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNewsletterSubscribers extends ListRecords
{
    protected static string $resource = NewsletterSubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Manual')
                ->modalHeading('Tambah Subscriber Manual')
                ->modalDescription('Untuk subscribe via API, gunakan endpoint /api/v1/newsletter/subscribe. Ini untuk add manual oleh admin.'),
        ];
    }
}