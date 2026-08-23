<?php

namespace App\Filament\Admin\Resources\Quotations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class QuotationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Hidden::make('quotable_type'),

                TextInput::make('title')
                    ->label('Judul Quotation')
                    ->required()
                    ->maxLength(191)
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->maxLength(5000)
                    ->columnSpanFull(),

                TextInput::make('discount')
                    ->label('Diskon (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->minValue(0),

                TextInput::make('tax')
                    ->label('PPN (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->minValue(0),

                DatePicker::make('valid_until')
                    ->label('Berlaku Sampai')
                    ->minDate(now())
                    ->default(now()->addDays(30)),

                Textarea::make('terms_conditions')
                    ->label('Syarat & Ketentuan')
                    ->rows(4)
                    ->helperText('Default terms akan digunakan jika dikosongkan.')
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->label('Catatan Internal')
                    ->rows(2)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}