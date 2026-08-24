<?php

namespace App\Filament\Admin\Resources\Faqs\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pertanyaan & Jawaban')
                    ->schema([
                        Select::make('category')
                            ->label('Kategori')
                            ->placeholder('Pilih kategori (opsional)')
                            ->options(\App\Models\Faq::query()->whereNotNull('category')->distinct()->pluck('category', 'category')->filter()->toArray() + [
                                'Umum' => 'Umum',
                                'Produk' => 'Produk',
                                'Pembayaran' => 'Pembayaran',
                                'Pengiriman' => 'Pengiriman',
                                'Garansi' => 'Garansi',
                                'Layanan' => 'Layanan',
                            ])
                            ->searchable()
                            ->createOptionForm([
                                TextInput::make('name')->required()->maxLength(191),
                            ])
                            ->createOptionUsing(fn (string $name) => \Illuminate\Support\Str::title($name)),

                        Textarea::make('question')
                            ->label('Pertanyaan')
                            ->required()
                            ->rows(2)
                            ->maxLength(500)
                            ->autofocus()
                            ->columnSpanFull(),

                        Textarea::make('answer')
                            ->label('Jawaban')
                            ->required()
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Pengaturan')
                    ->schema([
                        TextInput::make('sort')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Angka lebih kecil = tampil lebih dulu.'),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Off = tidak tampil di public FAQ.'),
                    ])
                    ->columns(2),
            ]);
    }
}