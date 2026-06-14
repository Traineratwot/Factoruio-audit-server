<?php

namespace App\Filament\Resources\Mods\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ModForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required(),

                TextInput::make('latest_version')
                    ->label('Latest Version'),

                TextInput::make('category')
                    ->label('Category'),

                TextInput::make('title')
                    ->label('Title'),

                TextInput::make('summary')
                    ->label('Summary'),

                TextInput::make('downloads_count')
                    ->label('Downloads Count'),

                TextInput::make('popularity')
                    ->label('Popularity')
                    ->numeric(),

                TextEntry::make('created_at')
                    ->label('Created Date')
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label('Last Modified Date')
                    ->dateTime(),
            ]);
    }
}
