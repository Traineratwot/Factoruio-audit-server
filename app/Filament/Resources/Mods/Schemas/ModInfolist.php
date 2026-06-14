<?php

namespace App\Filament\Resources\Mods\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ModInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('Id'),

                TextEntry::make('name')
                    ->label('Name'),

                TextEntry::make('latest_version')
                    ->label('Latest Version'),

                TextEntry::make('category')
                    ->label('Category'),

                TextEntry::make('title')
                    ->label('Title'),

                TextEntry::make('summary')
                    ->label('Summary'),

                TextEntry::make('downloads_count')
                    ->label('Downloads Count'),

                TextEntry::make('popularity')
                    ->label('Popularity'),

                TextEntry::make('created_at')
                    ->label('Created Date')
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label('Last Modified Date')
                    ->dateTime(),
            ]);
    }
}
