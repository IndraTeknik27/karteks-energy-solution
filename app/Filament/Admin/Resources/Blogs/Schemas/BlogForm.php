<?php

namespace App\Filament\Admin\Resources\Blogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BlogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konten')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
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
                            ->fileAttachmentsDirectory('blog-content')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'strike',
                                'bulletList', 'orderedList',
                                'h2', 'h3', 'link', 'blockquote',
                                'codeBlock', 'redo', 'undo',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Excerpt & Gambar')
                    ->schema([
                        Textarea::make('excerpt')
                            ->label('Ringkasan')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Ringkasan singkat yang muncul di listing & OG tags.')
                            ->columnSpanFull(),

                        FileUpload::make('featured_image')
                            ->label('Gambar Utama')
                            ->image()
                            ->imageEditor()
                            ->directory('blog-featured')
                            ->maxSize(5120)
                            ->helperText('Format: JPG/PNG/WebP. Maks 5MB.')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Metadata')
                    ->schema([
                        Select::make('blog_category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->required()->maxLength(191),
                                TextInput::make('slug')->required()->maxLength(191),
                            ])
                            ->placeholder('Pilih kategori'),

                        Select::make('author_id')
                            ->label('Penulis')
                            ->relationship('author', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => \Illuminate\Support\Facades\Auth::id())
                            ->placeholder('Pilih penulis')
                            ->required(),

                        TextInput::make('reading_time')
                            ->label('Waktu Baca (menit)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(60)
                            ->suffix('min')
                            ->placeholder('Auto-detect dari konten')
                            ->helperText('Kosongkan untuk auto-detect.'),

                        DateTimePicker::make('published_at')
                            ->label('Tanggal Publikasi')
                            ->native(false)
                            ->placeholder('Otomatis saat di-publish'),
                    ])
                    ->columns(2),

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
                            ->placeholder('Kosongkan untuk pakai excerpt')
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->columns(1),

                Section::make('Pengaturan')
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(false)
                            ->helperText('Off = draft (tidak muncul di public).'),

                        Toggle::make('is_featured')
                            ->label('Tampilkan sebagai Unggulan')
                            ->default(false)
                            ->helperText('On = muncul di homepage section "Blog Highlights".'),
                    ])
                    ->columns(2),
            ]);
    }
}