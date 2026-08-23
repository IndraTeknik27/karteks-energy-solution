<?php

namespace App\Filament\Admin\Resources\Coupons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode Kupon')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->autofocus()
                    ->helperText('Akan otomatis uppercase saat disimpan.')
                    ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                    ->columnSpanFull(),

                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(191)
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(2)
                    ->maxLength(1000)
                    ->columnSpanFull(),

                Select::make('type')
                    ->label('Tipe Diskon')
                    ->options([
                        'percent' => 'Persentase (%)',
                        'fixed' => 'Nominal Tetap (Rp)',
                    ])
                    ->required()
                    ->default('percent'),

                TextInput::make('value')
                    ->label('Nilai Diskon')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->suffix(fn ($get) => $get('type') === 'percent' ? '%' : 'Rp')
                    ->helperText(fn ($Get) => $Get('type') === 'percent' ? 'Persentase dari subtotal' : 'Potongan harga tetap dalam Rupiah'),

                TextInput::make('min_order_amount')
                    ->label('Minimum Order')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('Rp')
                    ->placeholder('0 = tanpa minimum'),

                TextInput::make('max_discount_amount')
                    ->label('Maksimum Diskon')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('Rp')
                    ->placeholder('Tanpa batas')
                    ->helperText('Hanya untuk tipe percent.'),

                TextInput::make('max_uses')
                    ->label('Maks Total Penggunaan')
                    ->numeric()
                    ->minValue(0)
                    ->placeholder('∞ unlimited'),

                TextInput::make('max_uses_per_customer')
                    ->label('Maks Per Customer')
                    ->numeric()
                    ->minValue(0)
                    ->placeholder('∞ unlimited'),

                DateTimePicker::make('starts_at')
                    ->label('Mulai Berlaku')
                    ->native(false),

                DateTimePicker::make('expires_at')
                    ->label('Berlaku Sampai')
                    ->native(false)
                    ->minDate(now()),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),

                Toggle::make('is_first_order_only')
                    ->label('Hanya untuk Order Pertama')
                    ->helperText('Customer hanya bisa pakai coupon ini di order pertama mereka.')
                    ->default(false),
            ])
            ->columns(2);
    }
}