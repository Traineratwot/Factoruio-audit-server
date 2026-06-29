<?php

namespace App\Filament\Resources\ModVersions\Schemas;

use App\Models\ModVersion;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ModVersionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->icon('heroicon-o-cube')
                    ->columns(2)
                    ->components([
                        TextEntry::make('id')
                            ->label('ID'),

                        TextEntry::make('mod.name')
                            ->label('Mod')
                            ->weight('bold'),

                        TextEntry::make('version')
                            ->label('Version')
                            ->badge()
                            ->color('success'),

                        TextEntry::make('factorio_version')
                            ->label('Factorio')
                            ->badge()
                            ->color('info'),
                    ]),

                Section::make('File')
                    ->icon('heroicon-o-document')
                    ->columns(2)
                    ->components([
                        TextEntry::make('file_name')
                            ->label('File Name')
                            ->columnSpanFull(),

                        TextEntry::make('download_url')
                            ->label('Download URL')
                            ->url(function (ModVersion $record): ?string {
                                if (! blank($record->download_url)) {
                                    return 'https://mods.factorio.com'.$record->download_url;
                                }

                                return null;
                            })
                            ->openUrlInNewTab()
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->columnSpanFull(),

                        TextEntry::make('sha1')
                            ->label('SHA1')
                            ->fontFamily('mono')
                            ->columnSpanFull(),
                    ]),

                Section::make('Dependencies')
                    ->icon('heroicon-o-link')
                    ->collapsible()
                    ->components([
                        TextEntry::make('dependencies')
                            ->label('Dependencies')
                            ->badge()
                            ->separator(',')
                            ->color('gray')
                            ->placeholder('No dependencies'),
                    ]),

                Section::make('Dates')
                    ->icon('heroicon-o-calendar')
                    ->collapsible()
                    ->components([
                        TextEntry::make('released_at')
                            ->label('Released')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('—'),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('d.m.Y H:i'),

                        TextEntry::make('updated_at')
                            ->label('Updated')
                            ->dateTime('d.m.Y H:i'),
                    ]),
            ]);
    }
}
