<?php

namespace App\Filament\Admin\Resources\Faqs\Tables;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FaqsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort')
                    ->label('#')
                    ->sortable()
                    ->alignCenter()
                    ->width(60),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color('info')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('question')
                    ->label('Pertanyaan')
                    ->searchable()
                    ->weight('bold')
                    ->wrap()
                    ->limit(80),

                TextColumn::make('answer')
                    ->label('Jawaban')
                    ->placeholder('—')
                    ->limit(60)
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y')
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
                Action::make('toggle')
                    ->label(fn ($r) => $r->is_active ? 'Nonaktifkan' : 'Aktifkan')
                    ->icon(fn ($r) => $r->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($r) => $r->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['is_active' => ! $record->is_active]);
                        $state = $record->is_active ? 'diaktifkan' : 'dinonaktifkan';
                        Notification::make()->title("FAQ {$state}")->success()->send();
                    }),
                DeleteAction::make(),
            ])
            ->defaultSort('sort');
    }
}