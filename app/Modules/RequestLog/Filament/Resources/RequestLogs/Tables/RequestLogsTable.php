<?php

namespace App\Modules\RequestLog\Filament\Resources\RequestLogs\Tables;

use App\Modules\RequestLog\Enums\TypeEnum;
use App\Modules\RequestLog\Models\RequestLog;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RequestLogsTable
{
    public static function table(Table $table): Table
    {
        $urlLimit = 20;

        return $table
            ->poll('30s')
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->description(fn (?RequestLog $record): string => $record?->created_at?->diffForHumans() ?? '-')
                    ->toggleable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('method')
                    ->label('Method')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('url')
                    ->label('URL')
                    ->limit($urlLimit)
                    ->copyable()
                    ->tooltip(fn (?RequestLog $record) => $record?->url)
                    ->state(fn (?RequestLog $record): string => Str::substr($record?->url ?? '', strlen($record?->url ?? '') - $urlLimit, $urlLimit))
                    ->copyableState(fn (?RequestLog $record) => $record?->url)
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('time')
                    ->label('Time')
                    ->state(fn (?RequestLog $record): string => round($record->time ?? 0, 2).'s')
                    ->sortable()
                    ->color(fn (?RequestLog $record): string => match (true) {
                        ($record->time ?? 0) <= 0 || ($record->time ?? 0) > 25 => 'danger',
                        ($record->time ?? 0) > 15 => 'warning',
                        ($record->time ?? 0) < 5 => 'success',
                        default => 'primary',
                    })
                    ->toggleable(),

                TextColumn::make('status_code')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->color(fn (?RequestLog $record): string => match (true) {
                        ($record->status_code ?? 0) >= 200 && ($record->status_code ?? 0) < 300 => 'success',
                        ($record->status_code ?? 0) <= 0 || ($record->status_code ?? 0) > 399 => 'danger',
                        ($record->status_code ?? 0) > 299 => 'warning',
                        default => 'primary',
                    })
                    ->copyable()
                    ->toggleable(),

                IconColumn::make('completed')
                    ->label('Completed')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('request_body')
                    ->label('Request Body')
                    ->copyable()
                    ->copyableState(fn (?RequestLog $record) => is_array($record?->request_body) ? json_encode($record->request_body) : $record->request_body)
                    ->limit($urlLimit)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('request_query')
                    ->label('Request Query')
                    ->copyable()
                    ->copyableState(fn (?RequestLog $record) => is_array($record?->request_query) ? json_encode($record->request_query) : $record->request_query)
                    ->limit($urlLimit)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('request_head')
                    ->label('Request Headers')
                    ->copyable()
                    ->copyableState(fn (?RequestLog $record) => $record?->request_head ? json_encode($record->request_head) : null)
                    ->state(fn (?RequestLog $record): ?string => $record?->request_head ? json_encode($record->request_head) : null)
                    ->limit($urlLimit)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('response_body')
                    ->label('Response Body')
                    ->copyable()
                    ->copyableState(fn (?RequestLog $record) => $record?->response_body)
                    ->limit($urlLimit)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('response_head')
                    ->label('Response Headers')
                    ->copyable()
                    ->copyableState(fn (?RequestLog $record) => $record?->response_head ? json_encode($record->response_head) : null)
                    ->state(fn (?RequestLog $record): ?string => $record?->response_head ? json_encode($record->response_head) : null)
                    ->limit($urlLimit)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(TypeEnum::class),

                SelectFilter::make('method')
                    ->label('Method')
                    ->options(
                        DB::table('request_logs')
                            ->whereNotNull('method')
                            ->select('method')
                            ->distinct()
                            ->pluck('method', 'method')
                            ->toArray(),
                    ),

                Filter::make('created_at')
                    ->schema([
                        DateTimePicker::make('from')->label('From'),
                        DateTimePicker::make('to')->label('To'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $from): Builder => $query->where('created_at', '>=', $from))
                            ->when($data['to'], fn (Builder $query, $to): Builder => $query->where('created_at', '<=', $to));
                    })
                    ->label('Created'),

                Filter::make('status_code')
                    ->schema([
                        TextInput::make('from')->label('Status from')->integer(),
                        TextInput::make('to')->label('Status to')->integer(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $from): Builder => $query->where('status_code', '>=', $from))
                            ->when($data['to'], fn (Builder $query, $to): Builder => $query->where('status_code', '<=', $to));
                    })
                    ->label('Status Code'),

                Filter::make('request')
                    ->schema([
                        TextInput::make('request')->label('Search in request body'),
                        TextInput::make('response')->label('Search in response body'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['request'], fn (Builder $query, $text): Builder => $query->where('request_body', 'LIKE', "%{$text}%"))
                            ->when($data['response'], fn (Builder $query, $text): Builder => $query->where('response_body', 'LIKE', "%{$text}%"));
                    }),

                TernaryFilter::make('completed')
                    ->label('Completed'),
            ], FiltersLayout::AboveContentCollapsible)

            ->recordActions([
                ViewAction::make()
                    ->iconButton(),
            ], RecordActionsPosition::BeforeCells);
    }
}
