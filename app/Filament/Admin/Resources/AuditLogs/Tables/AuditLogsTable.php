<?php

namespace App\Filament\Admin\Resources\AuditLogs\Tables;

use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->since()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),

                BadgeColumn::make('action')
                    ->label('Action')
                    ->colors([
                        'success' => 'create',
                        'warning' => 'update',
                        'danger' => 'delete',
                        'info' => 'login',
                        'gray' => 'logout',
                    ])
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable()
                    ->wrap()
                    ->limit(60)
                    ->placeholder('—'),

                TextColumn::make('model_type')
                    ->label('Model')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '—')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('model_id')
                    ->label('ID')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('method')
                    ->label('Method')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'GET' => 'info',
                        'POST' => 'success',
                        'PUT', 'PATCH' => 'warning',
                        'DELETE' => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('url')
                    ->label('URL')
                    ->placeholder('—')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label('Action')
                    ->options([
                        'create' => 'Create',
                        'update' => 'Update',
                        'delete' => 'Delete',
                        'login' => 'Login',
                        'logout' => 'Logout',
                    ])
                    ->multiple(),

                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('model_type')
                    ->label('Model')
                    ->query(fn (Builder $q, array $data) => $q->when(
                        $data['value'],
                        fn ($q) => $q->where('model_type', 'like', '%'.$data['value'].'%')
                    ))
                    ->form([
                        \Filament\Forms\Components\TextInput::make('value')
                            ->label('Model class contains')
                            ->placeholder('Order, Product, dst.'),
                    ]),

                Filter::make('created_at')
                    ->label('Hari ini')
                    ->query(fn (Builder $q) => $q->whereDate('created_at', today())),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25);
    }
}