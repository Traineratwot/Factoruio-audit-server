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
                    ->label('Id'),

                TextEntry::make('mod_name')
                    ->label('Mod Name'),

                TextEntry::make('mod_version')
                    ->label('Mod Version'),

                TextEntry::make('score')
                    ->label('Score'),

                TextEntry::make('raw')
                    ->label('Raw'),

                TextEntry::make('sha1')
                    ->label('Sha1'),

                TextEntry::make('created_at')
                    ->label('Created Date')
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label('Last Modified Date')
                    ->dateTime(),
            ]);
    }
}
