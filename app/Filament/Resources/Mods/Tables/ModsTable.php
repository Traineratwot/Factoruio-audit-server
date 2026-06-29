<?php

namespace App\Filament\Resources\Mods\Tables;

use App\Jobs\AuditJob;
use App\Models\Mod;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class ModsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('popularity', 'desc')
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Thumb')
                    ->state(fn(Mod $record)=> $record->getImage())
                    ->visibility('public')
                    ->circular()
                    ->imageSize(40)
                ,

                TextColumn::make('name')
                    ->label('Имя (slug)')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),

                TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('popularity')
                    ->label('Популярность')
                    ->sortable()
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.',
                        thousandsSeparator: ' ',
                    )
                    ->placeholder('0')
                    ->extraAttributes(['class' => 'font-mono']),

                TextColumn::make('latest_version')
                    ->label('Последняя версия')
                    ->badge()
                    ->color('success')
                    ->placeholder('—'),

                TextColumn::make('category')
                    ->label('Категория')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                TextColumn::make('tags')
                    ->label('Теги')
                    ->badge()
                    ->separator(',')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('summary')
                    ->label('Краткое описание')
                    ->limit(80)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) > 80) {
                            return $state;
                        }

                        return null;
                    })
                    ->placeholder('—'),

                TextColumn::make('downloads_count')
                    ->label('Количество загрузок')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.',
                        thousandsSeparator: ' ',
                    )
                    ->sortable()
                    ->placeholder('0'),

                TextColumn::make('factorio_version')
                    ->label('Factorio')
                    ->badge()
                    ->color('info')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('score')
                    ->label('Score')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Категория')
                    ->options(fn () => Mod::distinct()->pluck('category', 'category')->toArray())
                    ->placeholder('Все категории'),

                TernaryFilter::make('has_version')
                    ->label('Последняя версия')
                    ->placeholder('Все моды')
                    ->trueLabel('Имеют версию')
                    ->falseLabel('Без версии')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('latest_version')->where('latest_version', '!=', ''),
                        false: fn (Builder $query) => $query->whereNull('latest_version')->orWhere('latest_version', '=', ''),
                        blank: fn (Builder $query) => $query,
                    ),

                TernaryFilter::make('has_reports')
                    ->label('Есть отчет')
                    ->placeholder('Все моды')
                    ->trueLabel('Имеют отчет')
                    ->falseLabel('Без отчета')
                    ->queries(
                        true: fn (Builder|Mod $query) => $query->whereHas('reports'),
                        false: fn (Builder|Mod $query) => $query->whereDoesntHave('reports'),
                        blank: fn (Builder|Mod $query) => $query,
                    ),

                TernaryFilter::make('has_full_info')
                    ->label('Полная информация')
                    ->placeholder('Все моды')
                    ->trueLabel('Загружена')
                    ->falseLabel('Не загружена')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('fetch_full_info_at'),
                        false: fn (Builder $query) => $query->whereNull('fetch_full_info_at'),
                        blank: fn (Builder $query) => $query,
                    ),

                TernaryFilter::make('has_fetch_error')
                    ->label('Ошибки загрузки')
                    ->placeholder('Все моды')
                    ->trueLabel('Есть ошибки')
                    ->falseLabel('Без ошибок')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('fetch_full_info_error'),
                        false: fn (Builder $query) => $query->whereNull('fetch_full_info_error'),
                        blank: fn (Builder $query) => $query,
                    ),

                TernaryFilter::make('has_thumbnail')
                    ->label('Превью')
                    ->placeholder('Все моды')
                    ->trueLabel('Есть превью')
                    ->falseLabel('Без превью')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('thumbnail')->where('thumbnail', '!=', ''),
                        false: fn (Builder $query) => $query->whereNull('thumbnail')->orWhere('thumbnail', '=', ''),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->recordActions([
                Action::make('audit')
                    ->action(function (Mod $record) {
                        try {
                            $record->runAudit();
                        } catch (\Throwable $e) {
                            Log::error("Ошибка при запуске аудита для {$record->name}", ['exception' => $e]);
                            throw $e;
                        }
                    }),
            ], RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkAction::make('audits')
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            AuditJob::dispatch($record->id);
                        }
                    }),
            ]);
    }
}
