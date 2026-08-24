<?php

namespace App\Filament\Admin\Resources\Banners\Tables;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('desktop_image_url')
                    ->label('Banner')
                    ->height(60)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover'])
                    ->defaultImageUrl(asset('images/placeholder-banner.png')),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->weight('bold')
                    ->wrap()
                    ->limit(40),

                TextColumn::make('position')
                    ->label('Posisi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'home_hero' => 'success',
                        'home_secondary' => 'info',
                        'category_top' => 'warning',
                        'sidebar' => 'gray',
                        'popup' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => BannerPositionLabel($state))
                    ->sortable(),

                TextColumn::make('link_url')
                    ->label('CTA URL')
                    ->limit(30)
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('link_text')
                    ->label('Tombol')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('sort')
                    ->label('Urutan')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),

                IconColumn::make('is_currently_active')
                    ->label('Aktif')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->is_currently_active)
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn ($record) => $record->is_currently_active
                        ? 'Aktif & dalam window jadwal'
                        : 'Tidak aktif atau di luar window'),

                IconColumn::make('is_active')
                    ->label('Manual')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('starts_at')
                    ->label('Mulai')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('ends_at')
                    ->label('Berakhir')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('click_count')
                    ->label('Klik')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('position')
                    ->label('Posisi')
                    ->options(BannerPositionOptions()),

                Filter::make('is_currently_active')
                    ->label('Sedang Aktif')
                    ->query(fn (Builder $q) => $q->where('is_active', true)
                        ->where(function ($q) {
                            $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                        })
                        ->where(function ($q) {
                            $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                        })),

                Filter::make('scheduled')
                    ->label('Punya Jadwal')
                    ->query(fn (Builder $q) => $q->where(function ($q) {
                        $q->whereNotNull('starts_at')->orWhereNotNull('ends_at');
                    })),

                Filter::make('expired')
                    ->label('Sudah Kadaluarsa')
                    ->query(fn (Builder $q) => $q->whereNotNull('ends_at')->where('ends_at', '<', now())),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('toggle')
                    ->label(fn ($r) => $r->is_active ? 'Nonaktifkan' : 'Aktifkan')
                    ->icon(fn ($r) => $r->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($r) => $r->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['is_active' => ! $record->is_active]);
                        $state = $record->is_active ? 'diaktifkan' : 'dinonaktifkan';
                        Notification::make()->title("Banner {$state}")->success()->send();
                    }),
                DeleteAction::make(),
            ])
            ->defaultSort('position')
            ->defaultSort('sort')
            ->reorderable('sort');
    }
}

if (! function_exists('BannerPositionLabel')) {
    function BannerPositionLabel(string $value): string
    {
        return \App\Models\Banner::POSITIONS[$value] ?? ucfirst(str_replace('_', ' ', $value));
    }
}

if (! function_exists('BannerPositionOptions')) {
    function BannerPositionOptions(): array
    {
        return \App\Models\Banner::POSITIONS;
    }
}