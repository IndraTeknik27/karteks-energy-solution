<?php

namespace App\Filament\Admin\Resources\HomepageSections;

use App\Filament\Admin\Resources\HomepageSections\Pages\EditHomepageSection;
use App\Filament\Admin\Resources\HomepageSections\Pages\ListHomepageSections;
use App\Filament\Admin\Resources\HomepageSections\Schemas\HomepageSectionForm;
use App\Filament\Admin\Resources\HomepageSections\Tables\HomepageSectionsTable;
use App\Models\HomepageSection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HomepageSectionResource extends Resource
{
    protected static ?string $model = HomepageSection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    public static function getNavigationGroup(): ?string
    {
        return 'Konten & CMS';
    }

    protected static ?string $modelLabel = 'Homepage Section';

    protected static ?string $pluralModelLabel = 'Homepage Sections';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'homepage-sections';

    public static function form(Schema $schema): Schema
    {
        return HomepageSectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HomepageSectionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHomepageSections::route('/'),
            'edit' => EditHomepageSection::route('/{record}/edit'),
        ];
    }
}