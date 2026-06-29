<?php

namespace App\Filament\Resources\ModVersions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ModVersionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основная информация')
                    ->icon('heroicon-o-cube')
                    ->columns(2)
                    ->components([
                        Select::make('mod_id')
                            ->label('Мод')
                            ->relationship('mod', 'name')
                            ->searchable()
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('version')
                            ->label('Версия')
                            ->required()
                            ->placeholder('1.2.3'),

                        TextInput::make('factorio_version')
                            ->label('Версия Factorio')
                            ->required()
                            ->placeholder('2.0'),
                    ]),

                Section::make('Файл')
                    ->icon('heroicon-o-document')
                    ->columns(2)
                    ->components([
                        TextInput::make('file_name')
                            ->label('Имя файла')
                            ->required()
                            ->placeholder('mod-name_1.2.3.zip')
                            ->columnSpanFull(),

                        TextInput::make('download_url')
                            ->label('Ссылка на скачивание')
                            ->required()
                            ->url()
                            ->placeholder('/download/ModName_1.2.3.zip')
                            ->columnSpanFull(),

                        TextInput::make('sha1')
                            ->label('SHA1 хеш')
                            ->required()
                            ->extraAttributes(['class' => 'font-mono'])
                            ->placeholder('abc123...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Дополнительно')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->components([
                        DatePicker::make('released_at')
                            ->label('Дата релиза'),

                        TextEntry::make('created_at')
                            ->label('Создан')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->label('Обновлён')
                            ->dateTime(),
                    ]),
            ]);
    }
}
