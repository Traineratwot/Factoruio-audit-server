<?php

namespace App\Filament\Resources\Mods\Tables;

use App\Jobs\AuditJob;
use App\Models\Mod;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ModsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('popularity', 'desc')
            ->columns([
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
                    ->extraAttributes(['class' => 'font-mono']), // опционально

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
            ])
            ->filters([
                // Фильтр по категории (выбор из существующих)
                SelectFilter::make('category')
                    ->label('Категория')
                    ->options(fn() => Mod::distinct()->pluck('category', 'category')->toArray())
                    ->placeholder('Все категории'),

                // Фильтр по наличию последней версии (поле не пустое)
                TernaryFilter::make('has_version')
                    ->label('Последняя версия')
                    ->placeholder('Все моды')
                    ->trueLabel('Имеют версию')
                    ->falseLabel('Без версии')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('latest_version')->where('latest_version', '!=', ''),
                        false: fn(Builder $query) => $query->whereNull('latest_version')->orWhere('latest_version', '=', ''),
                        blank: fn(Builder $query) => $query,
                    ),

                // Фильтр по популярности (минимальное значение)
                Filter::make('popularity_min')
                    ->label('Минимальная популярность')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('min_popularity')
                            ->label('Не менее')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $query->when(
                        $data['min_popularity'],
                        fn(Builder $q, $value) => $q->where('popularity', '>=', $value),
                    )),
            ])
            ->recordActions([
                Action::make('audit')
                    ->action(function (Mod $record) {
                        AuditJob::dispatch($record->id);
                    })
            ], RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkAction::make('audits')
                    ->action(function ( $records) {
                        foreach ($records as $record) {
                            AuditJob::dispatch($record->id);
                        }
                    })
            ]);
    }
}
