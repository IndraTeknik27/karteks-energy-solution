<?php

namespace App\Filament\Admin\Resources\ContactMessages\Pages;

use App\Filament\Admin\Resources\ContactMessages\ContactMessageResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        // Auto-mark as read
        if (is_null($this->record->read_at)) {
            $this->record->update([
                'read_at' => now(),
                'status' => $this->record->status === 'new' ? 'read' : $this->record->status,
            ]);
        }
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pengirim')
                    ->schema([
                        TextEntry::make('name')->label('Nama'),
                        TextEntry::make('email')->label('Email')->copyable(),
                        TextEntry::make('phone')->label('Telepon')->placeholder('—')->copyable(),
                        TextEntry::make('ip_address')->label('IP Address')->placeholder('—'),
                    ])
                    ->columns(2),

                Section::make('Pesan')
                    ->schema([
                        TextEntry::make('subject')->label('Subjek')->weight('bold'),
                        TextEntry::make('message')->label('Isi Pesan')->prose()->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Balasan Admin')
                    ->schema([
                        TextEntry::make('admin_reply')
                            ->label('Balasan')
                            ->placeholder('Belum dibalas')
                            ->prose()
                            ->columnSpanFull(),
                        TextEntry::make('replied_at')->label('Dibalas Pada')->placeholder('—'),
                        TextEntry::make('repliedBy.name')->label('Dibalas Oleh')->placeholder('—'),
                    ])
                    ->columns(2),

                Section::make('Metadata')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'new' => 'warning',
                                'read' => 'info',
                                'replied' => 'success',
                                'archived' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'new' => 'Baru',
                                'read' => 'Dibaca',
                                'replied' => 'Dibalas',
                                'archived' => 'Diarsipkan',
                                default => $state,
                            }),
                        TextEntry::make('read_at')->label('Dibaca Pada')->placeholder('Belum dibaca'),
                        TextEntry::make('created_at')->label('Diterima')->since(),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reply')
                ->label('Balas via Email')
                ->icon('heroicon-o-envelope')
                ->color('success')
                ->visible(fn () => $this->record->status !== 'replied')
                ->form([
                    Textarea::make('reply_text')
                        ->label('Isi Balasan')
                        ->required()
                        ->rows(6)
                        ->placeholder('Tulis balasan untuk customer...'),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'admin_reply' => $data['reply_text'],
                        'replied_at' => now(),
                        'replied_by' => \Illuminate\Support\Facades\Auth::id(),
                        'status' => 'replied',
                    ]);
                    Notification::make()
                        ->title('Balasan tersimpan')
                        ->body('Catatan: email belum terkirim otomatis. Kirim manual dari mail client.')
                        ->success()
                        ->send();
                }),

            Action::make('mark_archived')
                ->label('Arsipkan')
                ->icon('heroicon-o-archive-box')
                ->color('gray')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status !== 'archived')
                ->action(function () {
                    $this->record->update(['status' => 'archived']);
                    Notification::make()->title('Pesan diarsipkan')->success()->send();
                }),

            DeleteAction::make(),
        ];
    }
}