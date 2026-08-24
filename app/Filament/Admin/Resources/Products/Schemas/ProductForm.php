<?php

namespace App\Filament\Admin\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Product')
                    ->tabs([
                        Tab::make('Informasi Dasar')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),

                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Product::class, 'slug', ignoreRecord: true),

                                TextInput::make('sku')
                                    ->maxLength(100)
                                    ->unique(Product::class, 'sku', ignoreRecord: true)
                                    ->helperText('Kosongkan jika tidak ada SKU'),

                                Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('brand_id')
                                    ->relationship('brand', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),

                                Textarea::make('short_description')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                RichEditor::make('description')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Harga & Stok')
                            ->schema([
                                TextInput::make('price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0),

                                TextInput::make('sale_price')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->helperText('Kosongkan jika tidak ada diskon'),

                                TextInput::make('cost_price')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->helperText('Untuk internal margin calculation'),

                                Select::make('stock_type')
                                    ->options([
                                        'track' => 'Track Stok',
                                        'booking' => 'Booking',
                                        'digital' => 'Digital',
                                        'service' => 'Jasa',
                                    ])
                                    ->default('track'),

                                Toggle::make('manage_stock')->default(true),
                                TextInput::make('stock_qty')
                                    ->numeric()
                                    ->default(0),
                                TextInput::make('low_stock_threshold')
                                    ->numeric()
                                    ->default(5),
                                Toggle::make('allow_backorder')->default(false),
                            ])
                            ->columns(2),

                        Tab::make('Media')
                            ->schema([
                                FileUpload::make('featured_image')
                                    ->label('Featured Image (Spatie Media: collection=featured)')
                                    ->disk('public')
                                    ->directory('products/featured')
                                    ->image()
                                    ->imageEditor()
                                    ->maxFiles(1),

                                FileUpload::make('gallery')
                                    ->label('Gallery (Spatie Media: collection=gallery)')
                                    ->disk('public')
                                    ->directory('products/gallery')
                                    ->image()
                                    ->multiple()
                                    ->reorderable()
                                    ->maxFiles(10),
                            ]),

                        Tab::make('Pengaturan')
                            ->schema([
                                Select::make('status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'published' => 'Published',
                                        'archived' => 'Archived',
                                    ])
                                    ->default('draft')
                                    ->required(),

                                Toggle::make('is_featured'),
                                Toggle::make('is_new_arrival'),
                                Toggle::make('is_bestseller'),

                                TextInput::make('weight')
                                    ->numeric()
                                    ->suffix('gram'),
                            ])
                            ->columns(2),

                        Tab::make('SEO')
                            ->schema([
                                TextInput::make('meta_title')->maxLength(255),
                                Textarea::make('meta_description')->rows(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}