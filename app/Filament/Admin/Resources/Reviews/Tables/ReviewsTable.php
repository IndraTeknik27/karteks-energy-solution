<?php

namespace App\Filament\Admin\Resources\Reviews\Tables;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->limit(30)
                    ->wrap(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn ($state) => str_repeat('★', (int) $state).str_repeat('☆', 5 - (int) $state))
                    ->color(fn ($state) => match (true) {
                        $state >= 4 => 'success',
                        $state == 3 => 'warning',
                        default => 'danger',
                    })
                    ->alignCenter(),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(40)
                    ->placeholder('(tanpa judul)'),

                TextColumn::make('content')
                    ->label('Konten')
                    ->limit(60)
                    ->wrap()
                    ->placeholder('—')
                    ->toggleable(),

                IconColumn::make('is_approved')
                    ->label('Approved')
                    ->boolean(),

                IconColumn::make('is_verified_purchase')
                    ->label('Verified')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('helpful_count')
                    ->label('Helpful')
                    ->numeric()
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('approved_at')
                    ->label('Approved At')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('rating')
                    ->options([
                        '5' => '5 ⭐',
                        '4' => '4 ⭐',
                        '3' => '3 ⭐',
                        '2' => '2 ⭐',
                        '1' => '1 ⭐',
                    ])
                    ->multiple(),

                Filter::make('pending')
                    ->label('Pending Approval')
                    ->query(fn (Builder $q) => $q->where('is_approved', false)),

                Filter::make('verified')
                    ->label('Verified Purchase')
                    ->query(fn (Builder $q) => $q->where('is_verified_purchase', true)),

                Filter::make('has_replies')
                    ->label('Ada Balasan')
                    ->query(fn (Builder $q) => $q->has('replies')),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($r) => ! $r->is_approved)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'is_approved' => true,
                            'approved_at' => now(),
                        ]);
                        Notification::make()->title('Review di-approve')->success()->send();
                    }),
                Action::make('unapprove')
                    ->label('Unapprove')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn ($record) => $record?->is_approved)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'is_approved' => false,
                            'approved_at' => null,
                        ]);
                        Notification::make()->title('Review di-unapprove')->warning()->send();
                    }),
                Action::make('delete')
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Review?')
                    ->modalDescription('Tindakan ini tidak dapat dibatalkan.')
                    ->action(function ($record) {
                        $record->delete();
                        Notification::make()->title('Review dihapus')->success()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}