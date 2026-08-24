<?php

namespace App\Filament\Admin\Resources\SiteSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SiteSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Key')
                    ->fontFamily('mono')
                    ->size(\Filament\Support\Enums\TextSize::Small)
                    ->color('gray')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('label')
                    ->label('Label')
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),

                BadgeColumn::make('group')
                    ->label('Group')
                    ->colors([
                        'info' => 'general',
                        'success' => 'contact',
                        'warning' => 'social',
                        'danger' => 'auth',
                        'primary' => 'ecommerce',
                        'gray' => 'seo',
                    ])
                    ->searchable(),

                BadgeColumn::make('type')
                    ->label('Tipe')
                    ->colors([
                        'info' => 'string',
                        'success' => 'text',
                        'warning' => 'integer',
                        'danger' => 'boolean',
                        'primary' => 'json',
                        'gray' => 'image',
                    ])
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),

                TextColumn::make('value')
                    ->label('Value')
                    ->limit(60)
                    ->placeholder('—')
                    ->wrap(),

                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean()
                    ->helperText('Public = bisa diakses API.'),

                TextColumn::make('sort')
                    ->label('Urutan')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->options(fn () => SiteSetting::query()->distinct()->pluck('group', 'group')->filter()->toArray()),
                SelectFilter::make('type')
                    ->options([
                        'string' => 'String',
                        'text' => 'Text',
                        'integer' => 'Integer',
                        'boolean' => 'Boolean',
                        'json' => 'JSON',
                        'image' => 'Image',
                    ]),
                Filter::make('is_public')
                    ->label('Public Only')
                    ->query(fn (Builder $q) => $q->where('is_public', true)),
            ])
            ->recordActions([
                EditAction::make()->label('Edit'),
            ])
            ->defaultSort('group')
            ->defaultSort('sort');
    }
}