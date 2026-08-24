<?php

namespace App\Filament\Admin\Resources\HomepageSections\Schemas;

use App\Models\HomepageSection;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HomepageSectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas')
                    ->schema([
                        TextInput::make('key')
                            ->label('Key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(191)
                            ->helperText('Identifier unik untuk section. Jangan diubah setelah dibuat.')
                            ->columnSpanFull()
                            ->disabled(fn ($record) => $record !== null),

                        Select::make('type')
                            ->label('Tipe Section')
                            ->options(collect(HomepageSection::typeOptions())
                                ->mapWithKeys(fn ($opts, $key) => [$key => $opts['label']])
                                ->all())
                            ->required()
                            ->reactive()
                            ->disabled(fn ($record) => $record !== null)
                            ->helperText('Tipe section menentukan partial yang akan di-render dan konfigurasi yang tersedia.'),

                        TextInput::make('title')
                            ->label('Judul Section')
                            ->maxLength(191)
                            ->placeholder('Kosongkan jika tidak perlu judul')
                            ->columnSpanFull(),

                        TextInput::make('subtitle')
                            ->label('Subjudul')
                            ->maxLength(191)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Konfigurasi Section')
                    ->description(fn ($get) => self::configHelperText($get('type')))
                    ->schema([
                        KeyValue::make('settings')
                            ->label('Settings (Key → Value)')
                            ->keyLabel('Setting Key')
                            ->valueLabel('Setting Value')
                            ->helperText('Format: key=value atau key=value1,value2 (array).')
                            ->addActionLabel('Tambah Setting')
                            ->reorderable()
                            ->columnSpanFull()
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if (! $record || ! $record->type) {
                                    return;
                                }
                                $defaults = $record->typeOptions()[$record->type]['defaults'] ?? [];
                                $merged = array_merge($defaults, $state ?? []);
                                $component->state($merged);
                            })
                            ->dehydrateStateUsing(function ($state, $record) {
                                $defaults = $record?->typeOptions()[$record->type]['defaults'] ?? [];
                                if (! $state) {
                                    return $defaults;
                                }
                                return $state;
                            }),
                    ]),

                Section::make('Tampilan')
                    ->schema([
                        TextInput::make('sort')
                            ->label('Urutan')
                            ->numeric()
                            ->default(100)
                            ->minValue(0)
                            ->helperText('Angka lebih kecil = tampil lebih dulu.'),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Off = section tidak dirender walaupun data tersedia.'),
                    ])
                    ->columns(2),
            ]);
    }

    protected static function configHelperText(?string $type): string
    {
        if (! $type) {
            return 'Pilih tipe section untuk melihat setting yang relevan.';
        }

        $option = HomepageSection::typeOptions()[$type] ?? null;
        if (! $option) {
            return '';
        }

        $defaults = $option['defaults'] ?? [];
        $lines = ["Default settings untuk {$option['label']}:"];

        foreach ($defaults as $k => $v) {
            if (is_bool($v)) {
                $v = $v ? 'true' : 'false';
            } elseif (is_array($v)) {
                $v = implode(',', $v);
            }
            $lines[] = "  • `{$k}` = `{$v}`";
        }

        return implode("\n", $lines);
    }
}