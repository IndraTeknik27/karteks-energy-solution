<?php

namespace App\Filament\Admin\Resources\NewsletterSubscribers\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class NewsletterSubscriberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Subscriber')
                    ->schema([
                        TextInput::make('email')
                            ->label('Email')
                            ->required()
                            ->email()
                            ->maxLength(191)
                            ->unique(ignoreRecord: true),

                        TextInput::make('name')
                            ->label('Nama')
                            ->maxLength(191)
                            ->placeholder('—'),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Off = subscriber dianggap sudah unsubscribe.'),
                    ])
                    ->columns(1),
            ]);
    }
}