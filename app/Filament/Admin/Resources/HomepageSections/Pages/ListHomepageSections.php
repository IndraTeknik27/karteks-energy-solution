<?php

namespace App\Filament\Admin\Resources\HomepageSections\Pages;

use App\Filament\Admin\Resources\HomepageSections\HomepageSectionResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListHomepageSections extends ListRecords
{
    protected static string $resource = HomepageSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reset_defaults')
                ->label('Reset ke Default')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Reset ke default?')
                ->modalDescription('Semua section yang ada di database akan di-reset ke konfigurasi default dari seeder. Perubahan manual akan hilang.')
                ->action(function () {
                    \Illuminate\Support\Facades\Artisan::call('db:seed', [
                        '--class' => 'Database\\Seeders\\HomepageSectionSeeder',
                        '--force' => true,
                    ]);
                    app(\App\Services\V1\HomepageService::class)->clearCache();
                    \Filament\Notifications\Notification::make()
                        ->title('Sections direset ke default')
                        ->success()
                        ->send();
                }),
            Action::make('clear_cache')
                ->label('Clear Cache')
                ->icon('heroicon-o-trash')
                ->color('gray')
                ->action(function () {
                    app(\App\Services\V1\HomepageService::class)->clearCache();
                    \Filament\Notifications\Notification::make()
                        ->title('Cache homepage dibersihkan')
                        ->success()
                        ->send();
                }),
        ];
    }
}