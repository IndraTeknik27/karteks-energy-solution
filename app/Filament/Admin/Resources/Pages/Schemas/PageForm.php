<?php

namespace App\Filament\Admin\Resources\Pages\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konten')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Halaman')
                            ->required()
                            ->maxLength(191)
                            ->autofocus()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('slug', \Illuminate\Support\Str::slug($state));
                                }
                            })
                            ->columnSpanFull(),

                        Hidden::make('slug'),

                        RichEditor::make('content')
                            ->label('Konten')
                            ->required()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('pages')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'strike',
                                'bulletList', 'orderedList',
                                'h2', 'h3', 'h4', 'link', 'blockquote',
                                'codeBlock', 'redo', 'undo',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Gambar Sampul')
                    ->schema([
                        FileUpload::make('featured_image')
                            ->label('Featured Image')
                            ->image()
                            ->imageEditor()
                            ->directory('pages')
                            ->maxSize(5120)
                            ->helperText('Format: JPG/PNG/WebP. Maks 5MB.')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('SEO')
                    ->description('Override meta tags default dari SeoService.')
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(191)
                            ->placeholder('Kosongkan untuk pakai judul')
                            ->columnSpanFull(),

                        Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(2)
                            ->maxLength(160)
                            ->placeholder('Kosongkan untuk auto-generate dari konten')
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->columns(1),

                Section::make('Pengaturan')
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(true)
                            ->helperText('Off = draft (return 404 di public).'),

                        Toggle::make('show_in_footer')
                            ->label('Tampil di Footer')
                            ->default(false)
                            ->helperText('On = muncul di kolom footer situs.'),

                        TextInput::make('sort')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Angka lebih kecil = tampil lebih dulu.'),
                    ])
                    ->columns(2),
            ]);
    }
}