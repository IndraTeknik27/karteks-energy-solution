<?php

namespace App\Filament\Admin\Resources\ContactMessages\Tables;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('read_at')
                    ->label('Read')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope-open')
                    ->falseIcon('heroicon-o-envelope')
                    ->trueColor('gray')
                    ->falseColor('warning'),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->limit(30),

                TextColumn::make('phone')
                    ->label('Telepon')
                    ->placeholder('—')
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('subject')
                    ->label('Subjek')
                    ->searchable()
                    ->limit(40)
                    ->wrap(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'new',
                        'info' => 'read',
                        'success' => 'replied',
                        'gray' => 'archived',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'Baru',
                        'read' => 'Dibaca',
                        'replied' => 'Dibalas',
                        'archived' => 'Diarsipkan',
                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Diterima')
                    ->dateTime('d M Y H:i')
                    ->since()
                    ->sortable(),

                TextColumn::make('replied_at')
                    ->label('Dibalas')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->since()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'new' => 'Baru',
                        'read' => 'Dibaca',
                        'replied' => 'Dibalas',
                        'archived' => 'Diarsipkan',
                    ])
                    ->multiple(),

                Filter::make('unread')
                    ->label('Belum Dibaca')
                    ->query(fn (Builder $q) => $q->whereNull('read_at')),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat')
                    ->icon('heroicon-o-eye'),
                Action::make('mark_read')
                    ->label('Tandai Dibaca')
                    ->icon('heroicon-o-envelope-open')
                    ->color('info')
                    ->visible(fn ($r) => is_null($r->read_at))
                    ->action(function ($record) {
                        $record->update([
                            'read_at' => now(),
                            'status' => $record->status === 'new' ? 'read' : $record->status,
                        ]);
                        Notification::make()->title('Pesan ditandai dibaca')->success()->send();
                    }),
                Action::make('archive')
                    ->label('Arsipkan')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn ($r) => $r->status !== 'archived')
                    ->action(function ($record) {
                        $record->update(['status' => 'archived']);
                        Notification::make()->title('Pesan diarsipkan')->success()->send();
                    }),
            ]);
    }
}