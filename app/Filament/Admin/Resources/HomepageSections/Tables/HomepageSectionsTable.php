<?php

namespace App\Filament\Admin\Resources\HomepageSections\Tables;

use App\Services\V1\HomepageService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HomepageSectionsTable
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

                TextColumn::make('type_label')
                    ->label('Tipe')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->fontFamily('mono')
                    ->size(\Filament\Support\Enums\TextSize::Small)
                    ->color('gray'),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->weight('bold')
                    ->placeholder('—')
                    ->wrap(),

                TextColumn::make('subtitle')
                    ->label('Subjudul')
                    ->placeholder('—')
                    ->limit(50)
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('toggle')
                    ->label(fn ($record) => $record?->is_active ? 'Nonaktifkan' : 'Aktifkan')
                    ->icon(fn ($record) => $record?->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($record) => $record?->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record = app(HomepageService::class)->toggleActive($record);
                        $state = $record->is_active ? 'diaktifkan' : 'dinonaktifkan';
                        Notification::make()->title("Section {$state}")->success()->send();
                    }),
                Action::make('moveUp')
                    ->label('Naik')
                    ->icon('heroicon-o-arrow-up')
                    ->color('gray')
                    ->action(function ($record) {
                        $prev = \App\Models\HomepageSection::where('sort', '<', $record->sort)
                            ->orderByDesc('sort')
                            ->first();
                        if ($prev) {
                            [$record->sort, $prev->sort] = [$prev->sort, $record->sort];
                            $record->save();
                            $prev->save();
                            app(HomepageService::class)->clearCache();
                        }
                        Notification::make()->title('Urutan diperbarui')->success()->send();
                    }),
                Action::make('moveDown')
                    ->label('Turun')
                    ->icon('heroicon-o-arrow-down')
                    ->color('gray')
                    ->action(function ($record) {
                        $next = \App\Models\HomepageSection::where('sort', '>', $record->sort)
                            ->orderBy('sort')
                            ->first();
                        if ($next) {
                            [$record->sort, $next->sort] = [$next->sort, $record->sort];
                            $record->save();
                            $next->save();
                            app(HomepageService::class)->clearCache();
                        }
                        Notification::make()->title('Urutan diperbarui')->success()->send();
                    }),
            ])
            ->defaultSort('sort');
    }
}