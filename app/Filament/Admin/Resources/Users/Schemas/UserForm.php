<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(User::class, 'email', ignoreRecord: true),

                TextInput::make('phone')
                    ->tel()
                    ->maxLength(20)
                    ->unique(User::class, 'phone', ignoreRecord: true),

                Select::make('gender')
                    ->options([
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                        'other' => 'Lainnya',
                    ])
                    ->nullable(),

                DatePicker::make('birth_date')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->maxDate(now()),

                FileUpload::make('avatar')
                    ->label('Avatar (Spatie Media)')
                    ->disk('public')
                    ->directory('avatars')
                    ->image()
                    ->imageEditor()
                    ->maxFiles(1),

                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->minLength(8)
                    ->helperText('Minimal 8 karakter. Kosongkan jika tidak ingin mengubah.'),

                Select::make('roles')
                    ->label('Roles')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload()
                    ->searchable(),

                Toggle::make('is_active')->default(true),
                Toggle::make('email_verified_at')
                    ->label('Email Verified')
                    ->dehydrateStateUsing(fn ($state) => $state ? now() : null)
                    ->dehydrated(fn ($state) => $state !== null),
            ]);
    }
}