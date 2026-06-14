<?php

namespace App\Filament\Resources\Reports\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('mod_name')
                    ->label('Mod Name')
                    ->required(),

                TextInput::make('mod_version')
                    ->label('Mod Version')
                    ->required(),

                TextInput::make('score')
                    ->label('Score')
                    ->required()
                    ->numeric(),

                TextInput::make('sha1')
                    ->label('Sha1')
                    ->required(),

                TextEntry::make('created_at')
                    ->label('Created Date')
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label('Last Modified Date')
                    ->dateTime(),
            ]);
    }
}
