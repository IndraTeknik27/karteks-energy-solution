<?php

namespace App\Filament\Admin\Resources\BlogCategories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BlogCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(191)
                            ->autofocus()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, ?string $old, ?\App\Models\BlogCategory $record) {
                                if ($record && $record->wasRecentlyCreated !== true) {
                                    return;
                                }
                                $set('slug', \Illuminate\Support\Str::slug($state));
                            }),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(191)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL-friendly identifier. Otomatis di-generate dari nama.')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Deskripsi & Gambar')
                    ->schema([
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        FileUpload::make('image')
                            ->label('Gambar Sampul')
                            ->image()
                            ->imageEditor()
                            ->directory('blog-categories')
                            ->maxSize(2048)
                            ->helperText('Format: JPG/PNG/WebP. Maks 2MB.')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Pengaturan')
                    ->schema([
                        TextInput::make('sort')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Angka lebih kecil = tampil lebih dulu.'),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}