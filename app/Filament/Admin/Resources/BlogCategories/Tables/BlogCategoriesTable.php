<?php

namespace App\Filament\Admin\Resources\BlogCategories\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BlogCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Gambar')
                    ->height(40)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover']),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->fontFamily('mono')
                    ->size(\Filament\Support\Enums\TextSize::Small)
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('blogs_count')
                    ->label('Jumlah Blog')
                    ->counts('blogs')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('sort')
                    ->label('Urutan')
                    ->sortable()
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y H:i')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('is_active')
                    ->label('Hanya Aktif')
                    ->query(fn (Builder $q) => $q->where('is_active', true)),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('sort');
    }
}