<?php

namespace App\Filament\Admin\Resources\NewsletterSubscribers;

use App\Filament\Admin\Resources\NewsletterSubscribers\Pages\ListNewsletterSubscribers;
use App\Filament\Admin\Resources\NewsletterSubscribers\Schemas\NewsletterSubscriberForm;
use App\Filament\Admin\Resources\NewsletterSubscribers\Tables\NewsletterSubscribersTable;
use App\Models\NewsletterSubscriber;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    public static function getNavigationGroup(): ?string
    {
        return 'Customer';
    }

    protected static ?string $modelLabel = 'Subscriber Newsletter';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'newsletter-subscribers';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::active()->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Schema $schema): Schema
    {
        return NewsletterSubscriberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NewsletterSubscribersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNewsletterSubscribers::route('/'),
        ];
    }
}