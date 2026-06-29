<?php

namespace App\Filament\Resources\Mods\Tables;

use App\Jobs\AuditJob;
use App\Models\Mod;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
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
                ImageColumn::make('thumbnail')
                    ->label('Thumb')
                    ->state(fn (Mod $record) => $record->getImage())
                    ->visibility('public')
                    ->circular()
                    ->imageSize(40),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('name')
                    ->label('Slug')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),

                TextColumn::make('popularity')
                    ->label('Popularity')
                    ->sortable()
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.',
                        thousandsSeparator: ' ',
                    )
                    ->placeholder('0')
                    ->extraAttributes(['class' => 'font-mono']),

                TextColumn::make('latest_version')
                    ->label('Latest Version')
                    ->badge()
                    ->color('success')
                    ->placeholder('—'),

                TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                TextColumn::make('tags')
                    ->label('Tags')
                    ->badge()
                    ->separator(',')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('summary')
                    ->label('Summary')
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
                    ->label('Downloads')
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
                    ->label('Category')
                    ->options(fn () => Mod::distinct()->pluck('category', 'category')->toArray())
                    ->placeholder('All categories'),

                TernaryFilter::make('has_version')
                    ->label('Latest Version')
                    ->placeholder('All mods')
                    ->trueLabel('Has version')
                    ->falseLabel('No version')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('latest_version')->where('latest_version', '!=', ''),
                        false: fn (Builder $query) => $query->whereNull('latest_version')->orWhere('latest_version', '=', ''),
                        blank: fn (Builder $query) => $query,
                    ),

                TernaryFilter::make('has_reports')
                    ->label('Has Report')
                    ->placeholder('All mods')
                    ->trueLabel('Has report')
                    ->falseLabel('No report')
                    ->queries(
                        true: fn (Builder|Mod $query) => $query->whereHas('reports'),
                        false: fn (Builder|Mod $query) => $query->whereDoesntHave('reports'),
                        blank: fn (Builder|Mod $query) => $query,
                    ),

                TernaryFilter::make('has_full_info')
                    ->label('Full Info')
                    ->placeholder('All mods')
                    ->trueLabel('Loaded')
                    ->falseLabel('Not loaded')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('fetch_full_info_at'),
                        false: fn (Builder $query) => $query->whereNull('fetch_full_info_at'),
                        blank: fn (Builder $query) => $query,
                    ),

                TernaryFilter::make('has_fetch_error')
                    ->label('Fetch Errors')
                    ->placeholder('All mods')
                    ->trueLabel('Has errors')
                    ->falseLabel('No errors')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('fetch_full_info_error'),
                        false: fn (Builder $query) => $query->whereNull('fetch_full_info_error'),
                        blank: fn (Builder $query) => $query,
                    ),

                TernaryFilter::make('has_thumbnail')
                    ->label('Thumbnail')
                    ->placeholder('All mods')
                    ->trueLabel('Has thumbnail')
                    ->falseLabel('No thumbnail')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('thumbnail')->where('thumbnail', '!=', ''),
                        false: fn (Builder $query) => $query->whereNull('thumbnail')->orWhere('thumbnail', '=', ''),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                ,
                Action::make('fetchFullInfo')
                    ->iconButton()
                    ->icon('heroicon-o-arrow-down-tray')
                    ->tooltip('Fetch Full Info')
                    ->color('info')
                    ->action(function (Mod $record): void {
                        $ok = $record->fetchFullInfo();

                        Notification::make()
                            ->title($ok ? 'Full info fetched' : 'Failed to fetch full info')
                            ->body($record->name)
                            ->{$ok ? 'success' : 'danger'}()
                            ->send();
                    }),

                Action::make('audit')
                    ->iconButton()
                    ->icon('heroicon-o-magnifying-glass')
                    ->tooltip('Audit')
                    ->color('warning')
                    ->action(function (Mod $record): void {
                        AuditJob::dispatch($record->id);

                        Notification::make()
                            ->title('Audit dispatched')
                            ->body($record->name)
                            ->success()
                            ->send();
                    }),
            ], RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkAction::make('fetchFullInfo')
                    ->label('Fetch Full Info')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($records): void {
                        foreach ($records as $record) {
                            $record->fetchFullInfo();
                        }

                        Notification::make()
                            ->title('Full info fetched for '.$records->count().' mods')
                            ->success()
                            ->send();
                    }),

                BulkAction::make('audit')
                    ->label('Run Audit')
                    ->icon('heroicon-o-magnifying-glass')
                    ->action(function ($records): void {
                        foreach ($records as $record) {
                            AuditJob::dispatch($record->id);
                        }

                        Notification::make()
                            ->title('Audit dispatched for '.$records->count().' mods')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
