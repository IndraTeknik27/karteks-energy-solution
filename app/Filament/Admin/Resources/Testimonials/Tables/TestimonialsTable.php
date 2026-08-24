<?php

namespace App\Filament\Admin\Resources\Testimonials\Tables;

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

class TestimonialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_url')
                    ->label('Foto')
                    ->height(48)
                    ->width(48)
                    ->extraImgAttributes(['class' => 'rounded-full object-cover']),

                TextColumn::make('customer_name')
                    ->label('Nama')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('position')
                    ->label('Posisi')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('company')
                    ->label('Perusahaan')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn ($state) => str_repeat('★', (int) $state) . str_repeat('☆', 5 - (int) $state))
                    ->color(fn ($state) => match (true) {
                        $state >= 4 => 'success',
                        $state == 3 => 'warning',
                        default => 'danger',
                    })
                    ->alignCenter(),

                TextColumn::make('content')
                    ->label('Testimoni')
                    ->limit(60)
                    ->wrap()
                    ->toggleable(),

                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('sort')
                    ->label('Urutan')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('is_active')
                    ->label('Hanya Aktif')
                    ->query(fn (Builder $q) => $q->where('is_active', true)),
                Filter::make('is_featured')
                    ->label('Unggulan')
                    ->query(fn (Builder $q) => $q->where('is_featured', true)),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('toggle')
                    ->label(fn ($record) => $record?->is_active ? 'Nonaktifkan' : 'Aktifkan')
                    ->icon(fn ($record) => $record?->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($record) => $record?->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['is_active' => ! $record->is_active]);
                        $state = $record->is_active ? 'diaktifkan' : 'dinonaktifkan';
                        Notification::make()->title("Testimoni {$state}")->success()->send();
                    }),
                DeleteAction::make(),
            ])
            ->defaultSort('sort');
    }
}