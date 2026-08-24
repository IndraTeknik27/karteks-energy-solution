<?php

namespace App\Filament\Admin\Resources\Menus\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MenuItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'allItems';

    protected static ?string $title = 'Menu Items';

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components(static::formSchema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('sort')
                    ->label('#')
                    ->sortable()
                    ->alignCenter()
                    ->width(50),

                TextColumn::make('parent.title')
                    ->label('Parent')
                    ->placeholder('— (root)')
                    ->toggleable(),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('url')
                    ->label('URL')
                    ->placeholder(fn ($record) => $record->route_name ? "Route: {$record->route_name}" : '—')
                    ->limit(40)
                    ->copyable(),

                TextColumn::make('route_name')
                    ->label('Route')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort')
            ->reorderable('sort')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Item'),
            ]);
    }

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->exists;
    }

    /**
     * Build a Filament form schema for create/edit menu item.
     */
    public static function formSchema(): array
    {
        return [
            Hidden::make('menu_id'),

            Section::make('Item')
                ->schema([
                    TextInput::make('title')
                        ->label('Judul')
                        ->required()
                        ->maxLength(191)
                        ->autofocus(),

                    Select::make('parent_id')
                        ->label('Parent (opsional)')
                        ->placeholder('Root item')
                        ->helperText('Pilih parent untuk sub-menu. Kosongkan untuk top-level.')
                        ->options(function ($get, $record) {
                            $menuId = $record?->menu_id ?? $get('menu_id');
                            if (! $menuId) {
                                return [];
                            }
                            return \App\Models\MenuItem::where('menu_id', $menuId)
                                ->whereNull('parent_id')
                                ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                ->pluck('title', 'id')
                                ->toArray();
                        })
                        ->searchable(),

                    TextInput::make('url')
                        ->label('URL (opsional)')
                        ->placeholder('/products atau https://...')
                        ->maxLength(500)
                        ->helperText('Pakai salah satu: URL atau Route Name (di bawah).'),

                    TextInput::make('route_name')
                        ->label('Route Name (opsional)')
                        ->placeholder('catalog.show, blog.index, dll.')
                        ->maxLength(191)
                        ->helperText('Laravel route name. Param via Route Params di bawah.'),

                    KeyValue::make('route_params')
                        ->label('Route Params')
                        ->helperText('Parameter untuk route (mis. slug => wuling-air-ev).')
                        ->addActionLabel('Tambah Param')
                        ->columnSpanFull(),

                    Select::make('target')
                        ->label('Target')
                        ->options([
                            '_self' => 'Tab yang sama',
                            '_blank' => 'Tab baru',
                        ])
                        ->default('_self'),

                    TextInput::make('icon')
                        ->label('Icon (opsional)')
                        ->placeholder('heroicon-o-home')
                        ->maxLength(100)
                        ->helperText('Heroicon class name.'),

                    TextInput::make('sort')
                        ->label('Urutan')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Angka lebih kecil = tampil lebih dulu.'),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),

                    KeyValue::make('meta')
                        ->label('Meta (custom)')
                        ->helperText('Custom key-value pairs untuk frontend styling.')
                        ->addActionLabel('Tambah Meta')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ];
    }
}