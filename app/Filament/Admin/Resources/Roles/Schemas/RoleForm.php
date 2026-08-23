<?php

namespace App\Filament\Admin\Resources\Roles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Permission;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                TextInput::make('guard_name')
                    ->required()
                    ->maxLength(255)
                    ->default('web'),

                \Filament\Forms\Components\CheckboxList::make('permissions')
                    ->label('Permissions')
                    ->relationship('permissions', 'name')
                    ->options(Permission::pluck('name', 'id'))
                    ->columns(3)
                    ->searchable()
                    ->bulkToggleable(),
            ]);
    }
}