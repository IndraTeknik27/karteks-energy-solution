<?php

namespace App\Filament\Admin\Resources\SiteSettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SiteSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas')
                    ->schema([
                        TextInput::make('label')
                            ->label('Label')
                            ->required()
                            ->maxLength(191),

                        TextInput::make('key')
                            ->label('Key')
                            ->disabled()
                            ->helperText('Identifier unik. Tidak bisa diubah setelah dibuat.')
                            ->dehydrated(),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Nilai Setting')
                    ->schema([
                        // Dynamic value field based on type - di-handle di mutate sebelum save
                        Textarea::make('value')
                            ->label('Value')
                            ->rows(fn ($get) => $get('type') === 'text' || $get('type') === 'json' ? 5 : 3)
                            ->helperText(fn ($get) => match ($get('type')) {
                                'boolean' => '0 atau 1',
                                'integer' => 'Angka bulat',
                                'json', 'array' => 'Format JSON valid',
                                'image' => 'Upload gambar di bawah',
                                default => 'Teks bebas',
                            })
                            ->visible(fn ($get) => $get('type') !== 'image')
                            ->columnSpanFull(),

                        FileUpload::make('image_value')
                            ->label('Image Value')
                            ->image()
                            ->directory('site-settings')
                            ->helperText('Hanya untuk setting bertipe image.')
                            ->visible(fn ($get) => $get('type') === 'image')
                            ->columnSpanFull(),

                        Toggle::make('is_public')
                            ->label('Public')
                            ->helperText('On = bisa diakses dari public API.'),
                    ])
                    ->columns(1),

                Section::make('Pengaturan')
                    ->schema([
                        TextInput::make('sort')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(1),
            ]);
    }
}