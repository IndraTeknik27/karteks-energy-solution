<?php

namespace App\Filament\Admin\Resources\Services\Schemas;

use App\Models\Service;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(Service::class, 'slug', ignoreRecord: true),

                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Select::make('pricing_type')
                    ->required()
                    ->options([
                        'fixed' => 'Harga Pasti',
                        'starting_price' => 'Harga Mulai Dari',
                        'quotation' => 'Berdasarkan Quotation',
                        'free' => 'Gratis',
                    ])
                    ->default('fixed')
                    ->live(),

                TextInput::make('base_price')
                    ->numeric()
                    ->prefix('Rp')
                    ->visible(fn ($get) => $get('pricing_type') === 'fixed'),

                TextInput::make('starting_price')
                    ->numeric()
                    ->prefix('Rp')
                    ->visible(fn ($get) => $get('pricing_type') === 'starting_price'),

                TextInput::make('duration_minutes')
                    ->numeric()
                    ->suffix('menit'),

                FileUpload::make('image')
                    ->disk('public')
                    ->directory('services')
                    ->image()
                    ->imageEditor(),

                Textarea::make('short_description')
                    ->rows(2)
                    ->columnSpanFull(),

                RichEditor::make('description')
                    ->columnSpanFull(),

                TextInput::make('sort')->numeric()->default(0),

                Toggle::make('is_active')->default(true),
                Toggle::make('is_featured')->default(false),

                TextInput::make('meta_title')->maxLength(255),
                Textarea::make('meta_description')->rows(2)->columnSpanFull(),
            ]);
    }
}