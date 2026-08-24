<?php

namespace App\Filament\Admin\Resources\SiteSettings;

use App\Filament\Admin\Resources\SiteSettings\Pages\EditSiteSetting;
use App\Filament\Admin\Resources\SiteSettings\Pages\ListSiteSettings;
use App\Filament\Admin\Resources\SiteSettings\Tables\SiteSettingsTable;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    public static function getNavigationGroup(): ?string
    {
        return 'Pengaturan';
    }

    protected static ?string $modelLabel = 'Pengaturan Situs';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'site-settings';

    // No create page - settings di-seed via SiteSettingSeeder
    public static function table(Table $table): Table
    {
        return SiteSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSiteSettings::route('/'),
            'edit' => EditSiteSetting::route('/{record}/edit'),
        ];
    }
}