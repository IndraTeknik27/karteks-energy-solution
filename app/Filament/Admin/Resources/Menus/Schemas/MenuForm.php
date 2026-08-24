<?php

namespace App\Filament\Admin\Resources\Menus\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Menu')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Menu')
                            ->required()
                            ->maxLength(191)
                            ->autofocus()
                            ->helperText('Nama internal, mis. "Header Utama" atau "Footer Links".'),

                        Select::make('location')
                            ->label('Lokasi')
                            ->required()
                            ->options([
                                'header' => 'Header',
                                'footer' => 'Footer',
                                'sidebar' => 'Sidebar',
                                'mobile' => 'Mobile Drawer',
                            ])
                            ->unique(ignoreRecord: true)
                            ->helperText('Dimana menu ini akan ditampilkan.'),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Pengaturan')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Off = menu tidak di-render di frontend.'),
                    ])
                    ->columns(1),
            ]);
    }
}