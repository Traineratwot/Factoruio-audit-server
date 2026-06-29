<?php

namespace App\Filament\Resources\Mods\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ModInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основная информация')
                    ->columns(2)
                    ->components([
                        TextEntry::make('id')
                            ->label('Id'),

                        TextEntry::make('name')
                            ->label('Name'),

                        TextEntry::make('title')
                            ->label('Title')
                            ->columnSpanFull(),

                        TextEntry::make('summary')
                            ->label('Summary')
                            ->columnSpanFull(),

                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->hiddenLabel(),

                        TextEntry::make('owner')
                            ->label('Owner'),

                        TextEntry::make('category')
                            ->label('Category')
                            ->badge(),

                        TextEntry::make('latest_version')
                            ->label('Последняя версия')
                            ->badge()
                            ->color('success'),

                        TextEntry::make('factorio_version')
                            ->label('Версия Factorio')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('downloads_count')
                            ->label('Загрузки')
                            ->numeric(
                                decimalPlaces: 0,
                                decimalSeparator: '.',
                                thousandsSeparator: ' ',
                            ),

                        TextEntry::make('popularity')
                            ->label('Популярность')
                            ->numeric(
                                decimalPlaces: 0,
                                decimalSeparator: '.',
                                thousandsSeparator: ' ',
                            ),

                        TextEntry::make('score')
                            ->label('Score'),

                        TextEntry::make('homepage')
                            ->label('Homepage')
                            ->url(fn(string|null|false $value) => !empty($value) ?: '#', true)
                            ->columnSpanFull(),
                    ]),

                Section::make('Изображение')
                    ->components([
                        ImageEntry::make('thumbnail')
                            ->label('Thumbnail'),
                    ]),

                Section::make('Теги')
                    ->components([
                        TextEntry::make('tags')
                            ->label('Tags')
                            ->badge()
                            ->separator(','),
                    ]),

                Section::make('Лицензия')
                    ->components([
                        TextEntry::make('license.name')
                            ->label('License'),
                        TextEntry::make('license.title')
                            ->label('Title'),
                        TextEntry::make('license.url')
                            ->label('URL')
                            ->url(fn(string|null|false $value) => !empty($value) ?: '#', true)
                        ,
                    ]),

                Section::make('Изображения')
                    ->collapsible()
                    ->components([
                        TextEntry::make('images')
                            ->label('Images')
                            ->hiddenLabel(),
                    ]),

                Section::make('Хронология')
                    ->collapsible()
                    ->components([
                        TextEntry::make('changelog')
                            ->label('Changelog')
                            ->hiddenLabel(),
                    ]),

                TextEntry::make('created_at')
                    ->label('Created Date')
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label('Last Modified Date')
                    ->dateTime(),
            ]);
    }
}
