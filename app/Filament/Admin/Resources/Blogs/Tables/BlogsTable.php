<?php

namespace App\Filament\Admin\Resources\Blogs\Tables;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BlogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('featured_image_url')
                    ->label('Gambar')
                    ->height(48)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover']),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->weight('bold')
                    ->wrap()
                    ->limit(50),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('info')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('author.name')
                    ->label('Penulis')
                    ->placeholder('—')
                    ->toggleable(),

                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),

                TextColumn::make('reading_time')
                    ->label('Waktu Baca')
                    ->suffix(' min')
                    ->alignCenter()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('views')
                    ->label('Views')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('published_at')
                    ->label('Dipublikasikan')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum')
                    ->since()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y H:i')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('blog_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),

                Filter::make('is_published')
                    ->label('Published')
                    ->query(fn (Builder $q) => $q->where('is_published', true)),

                Filter::make('is_featured')
                    ->label('Unggulan')
                    ->query(fn (Builder $q) => $q->where('is_featured', true)),

                Filter::make('draft')
                    ->label('Draft')
                    ->query(fn (Builder $q) => $q->where('is_published', false)),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('toggle_publish')
                    ->label(fn ($record) => $record?->is_published ? 'Unpublish' : 'Publish')
                    ->icon(fn ($record) => $record?->is_published ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($record) => $record?->is_published ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'is_published' => ! $record->is_published,
                            'published_at' => ! $record->is_published && ! $record->published_at ? now() : $record->published_at,
                        ]);
                        $state = $record->is_published ? 'dipublikasikan' : 'di-unpublish';
                        Notification::make()->title("Artikel {$state}")->success()->send();
                    }),
                DeleteAction::make(),
            ])
            ->defaultSort('published_at', 'desc');
    }
}