<?php

namespace App\Filament\Resources\Mods\RelationManagers;

use App\Filament\Resources\Reports\Tables\ReportsTable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ModReportRelationManager extends RelationManager
{
    protected static string $relationship = 'reports';

    public function infolist(Schema $schema): Schema
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

    public function table(Table $table): Table
    {
        return ReportsTable::table($table);
    }
}
