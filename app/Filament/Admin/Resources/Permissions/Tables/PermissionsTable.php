<?php

namespace App\Filament\Admin\Resources\Permissions\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('guard_name')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('roles_count')
                    ->counts('roles')
                    ->badge()
                    ->color('info'),
            ])
            ->defaultSort('name')
            ->paginated([25, 50, 100, 200]);
    }
}