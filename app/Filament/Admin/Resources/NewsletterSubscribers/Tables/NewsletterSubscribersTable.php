<?php

namespace App\Filament\Admin\Resources\NewsletterSubscribers\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NewsletterSubscribersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Nama')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('subscribed_at')
                    ->label('Subscribed')
                    ->dateTime('d M Y H:i')
                    ->since()
                    ->sortable(),

                TextColumn::make('unsubscribed_at')
                    ->label('Unsubscribed')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('is_active')
                    ->label('Hanya Aktif')
                    ->query(fn (Builder $q) => $q->where('is_active', true)),
                Filter::make('unsubscribed')
                    ->label('Sudah Unsubscribe')
                    ->query(fn (Builder $q) => $q->whereNotNull('unsubscribed_at')),
            ])
            ->recordActions([
                Action::make('toggle')
                    ->label(fn ($record) => $record?->is_active ? 'Nonaktifkan' : 'Aktifkan')
                    ->icon(fn ($record) => $record?->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($record) => $record?->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'is_active' => ! $record->is_active,
                            'unsubscribed_at' => ! $record->is_active ? now() : null,
                        ]);
                        $state = $record->is_active ? 'diaktifkan' : 'di-unsubscribe';
                        Notification::make()->title("Subscriber {$state}")->success()->send();
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('export_csv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        $subscribers = NewsletterSubscriberResource::getModel()::query()
                            ->where('is_active', true)
                            ->orderBy('subscribed_at', 'desc')
                            ->get();

                        $filename = 'newsletter-subscribers-'.now()->format('Y-m-d').'.csv';
                        $headers = [
                            'Content-Type' => 'text/csv; charset=utf-8',
                            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                        ];

                        $callback = function () use ($subscribers) {
                            $file = fopen('php://output', 'w');
                            fputcsv($file, ['Email', 'Name', 'Subscribed At', 'IP']);
                            foreach ($subscribers as $s) {
                                fputcsv($file, [
                                    $s->email,
                                    $s->name,
                                    $s->subscribed_at?->format('Y-m-d H:i:s'),
                                    $s->ip_address,
                                ]);
                            }
                            fclose($file);
                        };

                        return response()->stream($callback, 200, $headers);
                    }),
            ])
            ->defaultSort('subscribed_at', 'desc');
    }
}