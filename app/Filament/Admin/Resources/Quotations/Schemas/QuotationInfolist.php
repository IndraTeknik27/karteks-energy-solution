<?php

namespace App\Filament\Admin\Resources\Quotations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class QuotationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('quotation_number')
                    ->label('Quotation Number')
                    ->copyable()
                    ->weight('bold'),

                TextEntry::make('customer.name')
                    ->label('Customer'),

                TextEntry::make('customer.email')
                    ->label('Email')
                    ->copyable(),

                TextEntry::make('title')
                    ->label('Judul')
                    ->columnSpanFull(),

                TextEntry::make('description')
                    ->label('Deskripsi')
                    ->placeholder('—')
                    ->columnSpanFull(),

                TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'viewed' => 'warning',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'expired' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => strtoupper(str_replace('_', ' ', $state))),

                TextEntry::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR'),

                TextEntry::make('discount')
                    ->label('Diskon')
                    ->money('IDR'),

                TextEntry::make('tax')
                    ->label('PPN')
                    ->money('IDR'),

                TextEntry::make('total')
                    ->label('Total')
                    ->money('IDR')
                    ->weight('bold'),

                TextEntry::make('valid_until')
                    ->label('Berlaku Sampai')
                    ->date('d F Y')
                    ->placeholder('—'),

                TextEntry::make('terms_conditions')
                    ->label('Syarat & Ketentuan')
                    ->placeholder('—')
                    ->columnSpanFull(),

                TextEntry::make('notes')
                    ->label('Catatan')
                    ->placeholder('—')
                    ->columnSpanFull(),

                TextEntry::make('sent_at')
                    ->label('Dikirim')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—'),

                TextEntry::make('viewed_at')
                    ->label('Dilihat Customer')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—'),

                TextEntry::make('accepted_at')
                    ->label('Diterima')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—'),

                TextEntry::make('rejected_at')
                    ->label('Ditolak')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—'),

                TextEntry::make('rejection_reason')
                    ->label('Alasan Penolakan')
                    ->placeholder('—')
                    ->visible(fn ($record) => $record?->rejection_reason)
                    ->columnSpanFull(),
            ])
            ->columns(3);
    }
}