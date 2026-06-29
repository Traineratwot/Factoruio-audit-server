<?php

namespace App\Filament\Resources\Reports\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),

                TextEntry::make('mod_name')
                    ->label('Mod'),

                TextEntry::make('mod_version')
                    ->label('Version'),

                TextEntry::make('score')
                    ->label('Score'),

                TextEntry::make('raw')
                    ->label('Raw Data'),

                TextEntry::make('sha1')
                    ->label('SHA1'),

                TextEntry::make('created_at')
                    ->label('Created')
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label('Last Modified')
                    ->dateTime(),
            ]);
    }
}
