<?php

namespace App\Filament\Admin\Resources\Banners\Schemas;

use App\Models\Banner;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konten')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(191)
                            ->autofocus()
                            ->columnSpanFull(),

                        TextInput::make('subtitle')
                            ->label('Subjudul')
                            ->maxLength(191)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Gambar Banner')
                    ->description('Upload gambar desktop (1920x600px ideal). Gambar mobile akan otomatis di-crop atau bisa di-upload terpisah.')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('desktop')
                            ->label('Gambar Desktop')
                            ->collection('desktop')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatioOptions(['16:5', '16:4', '16:3', '3:1'])
                            ->required()
                            ->maxSize(5120)
                            ->helperText('Format: JPG/PNG/WebP. Maks 5MB.')
                            ->columnSpanFull(),

                        SpatieMediaLibraryFileUpload::make('mobile')
                            ->label('Gambar Mobile (Opsional)')
                            ->collection('mobile')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatioOptions(['3:4', '4:5', '1:1'])
                            ->maxSize(5120)
                            ->helperText('Jika kosong, akan pakai gambar desktop yang di-crop.')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('CTA & Tautan')
                    ->schema([
                        TextInput::make('link_url')
                            ->label('URL Tujuan')
                            ->placeholder('/products atau https://...')
                            ->url()
                            ->maxLength(500)
                            ->helperText('Pakai path relatif (/products) atau URL penuh.')
                            ->columnSpanFull(),

                        TextInput::make('link_text')
                            ->label('Teks Tombol CTA')
                            ->placeholder('Lihat Produk, Konsultasi, dll.')
                            ->maxLength(100)
                            ->columnSpanFull(),

                        Select::make('link_target')
                            ->label('Target Link')
                            ->options([
                                '_self' => 'Tab yang sama',
                                '_blank' => 'Tab baru',
                            ])
                            ->default('_self'),
                    ])
                    ->columns(2),

                Section::make('Penjadwalan & Tampilan')
                    ->schema([
                        Select::make('position')
                            ->label('Posisi Banner')
                            ->options(Banner::POSITIONS)
                            ->required()
                            ->default(Banner::POSITION_HOME_HERO)
                            ->helperText('Dimana banner ini akan ditampilkan.'),

                        TextInput::make('sort')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Angka lebih kecil = tampil lebih dulu.'),

                        DateTimePicker::make('starts_at')
                            ->label('Mulai Ditampilkan')
                            ->native(false)
                            ->helperText('Kosongkan = selalu tampil (selama aktif).'),

                        DateTimePicker::make('ends_at')
                            ->label('Berakhir Ditampilkan')
                            ->native(false)
                            ->helperText('Kosongkan = tidak ada batas waktu.'),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Off = banner tidak ditampilkan walaupun dalam window jadwal.'),
                    ])
                    ->columns(2),
            ]);
    }
}