<?php

namespace App\Filament\Admin\Resources\Pages\Tables;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PagesTable
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
                    ->limit(40),

                TextColumn::make('slug')
                    ->label('URL')
                    ->prefix('/page/')
                    ->fontFamily('mono')
                    ->size(\Filament\Support\Enums\TextSize::Small)
                    ->color('gray')
                    ->copyable()
                    ->searchable(),

                IconColumn::make('show_in_footer')
                    ->label('Footer')
                    ->boolean(),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),

                TextColumn::make('sort')
                    ->label('Urutan')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y H:i')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('is_published')
                    ->label('Published')
                    ->query(fn (Builder $q) => $q->where('is_published', true)),
                Filter::make('show_in_footer')
                    ->label('Tampil di Footer')
                    ->query(fn (Builder $q) => $q->where('show_in_footer', true)),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('toggle_publish')
                    ->label(fn ($r) => $r->is_published ? 'Unpublish' : 'Publish')
                    ->icon(fn ($r) => $r->is_published ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($r) => $r->is_published ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['is_published' => ! $record->is_published]);
                        $state = $record->is_published ? 'dipublikasikan' : 'di-unpublish';
                        Notification::make()->title("Halaman {$state}")->success()->send();
                    }),
                DeleteAction::make(),
            ])
            ->defaultSort('sort');
    }
}