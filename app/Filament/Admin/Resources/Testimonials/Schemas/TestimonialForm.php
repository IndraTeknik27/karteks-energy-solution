<?php

namespace App\Filament\Admin\Resources\Testimonials\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Pelanggan')
                    ->schema([
                        TextInput::make('customer_name')
                            ->label('Nama Pelanggan')
                            ->required()
                            ->maxLength(191)
                            ->autofocus()
                            ->columnSpanFull(),

                        TextInput::make('customer_email')
                            ->label('Email (opsional)')
                            ->email()
                            ->maxLength(191)
                            ->placeholder('customer@example.com')
                            ->helperText('Tidak dipublikasikan. Hanya untuk tracking internal.'),

                        TextInput::make('position')
                            ->label('Posisi / Jabatan')
                            ->maxLength(191)
                            ->placeholder('Pemilik, Manager, dll.'),

                        TextInput::make('company')
                            ->label('Perusahaan / Toko')
                            ->maxLength(191)
                            ->placeholder('Nama perusahaan / toko'),
                    ])
                    ->columns(2),

                Section::make('Testimoni')
                    ->schema([
                        Select::make('rating')
                            ->label('Rating')
                            ->options([
                                5 => '★★★★★ (5/5) - Sangat Puas',
                                4 => '★★★★☆ (4/5) - Puas',
                                3 => '★★★☆☆ (3/5) - Cukup',
                                2 => '★★☆☆☆ (2/5) - Kurang',
                                1 => '★☆☆☆☆ (1/5) - Tidak Puas',
                            ])
                            ->default(5)
                            ->required(),

                        Textarea::make('content')
                            ->label('Testimoni')
                            ->required()
                            ->rows(5)
                            ->maxLength(1000)
                            ->placeholder('"Pelayanan KARTEKS sangat memuaskan..."')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Media')
                    ->schema([
                        FileUpload::make('photo')
                            ->label('Foto Pelanggan')
                            ->collection('photo')
                            ->image()
                            ->imageEditor()
                            ->directory('testimonials')
                            ->maxSize(2048)
                            ->helperText('Format: JPG/PNG. Maks 2MB. Akan otomatis di-crop 1:1.')
                            ->columnSpanFull(),

                        TextInput::make('customer_photo')
                            ->label('URL Foto (alternatif)')
                            ->url()
                            ->maxLength(500)
                            ->placeholder('https://...')
                            ->helperText('Opsional. Jika upload di atas, field ini di-override.')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Pengaturan')
                    ->schema([
                        TextInput::make('sort')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Off = tidak tampil di public.'),

                        Toggle::make('is_featured')
                            ->label('Unggulan')
                            ->default(false)
                            ->helperText('On = diprioritaskan di section "Testimoni" homepage.'),
                    ])
                    ->columns(2),
            ]);
    }
}