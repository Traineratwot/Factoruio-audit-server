<?php

namespace App\Filament\Resources\Authors\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuthorsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ]);
    }
}
