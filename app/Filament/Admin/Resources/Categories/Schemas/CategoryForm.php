<?php

namespace App\Filament\Admin\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
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
                    ->unique(Category::class, 'slug', ignoreRecord: true),

                Select::make('parent_id')
                    ->label('Parent Category')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->placeholder('— Root Category —'),

                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('icon')
                    ->maxLength(100)
                    ->placeholder('heroicon-o-squares-2x2')
                    ->helperText('Heroicon name'),

                FileUpload::make('image')
                    ->disk('public')
                    ->directory('categories')
                    ->image()
                    ->imageEditor(),

                TextInput::make('sort')
                    ->numeric()
                    ->default(0),

                Toggle::make('is_active')
                    ->default(true),
                Toggle::make('is_featured')
                    ->default(false),

                TextInput::make('meta_title')
                    ->maxLength(255),
                Textarea::make('meta_description')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }
}