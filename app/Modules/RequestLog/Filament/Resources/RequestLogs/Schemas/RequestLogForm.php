<?php

namespace App\Modules\RequestLog\Filament\Resources\RequestLogs\Schemas;

use App\Modules\RequestLog\Models\RequestLog;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Casts\Json;

class RequestLogForm
{
    public static function configure(Schema $schema): Schema
    {
        /** @var RequestLog $record */
        $record = $schema->getRecord();

        $request = [];
        $response = [];

        $request[] = CodeEditor::make('request_head')
            ->wrap()
            ->formatStateUsing(fn () => Json::encode($record->request_head, JSON_PRETTY_PRINT))
            ->language(Language::Json);

        $response[] = CodeEditor::make('response_head')
            ->wrap()
            ->formatStateUsing(fn () => Json::encode($record->response_head, JSON_PRETTY_PRINT))
            ->language(Language::Json);

        $request_body = $record->getRequestBody();
        $request_query = $record->getRequestQuery();
        $response_body = $record->getResponseBody();

        if (is_array($request_query)) {
            $request[] = CodeEditor::make('request_query')
                ->wrap()
                ->language(Language::Json)
                ->formatStateUsing(fn () => Json::encode($request_query, JSON_PRETTY_PRINT))
                ->label('Request Query');
        } else {
            $request[] = CodeEditor::make('request_query')
                ->wrap()
                ->label('Request Query');
        }

        if (is_array($request_body)) {
            $request[] = CodeEditor::make('request_body')
                ->wrap()
                ->language(Language::Json)
                ->formatStateUsing(fn () => Json::encode($request_body, JSON_PRETTY_PRINT))
                ->label('Request Body');
        } else {
            $request[] = CodeEditor::make('request_body')
                ->wrap()
                ->label('Request Body');
        }

        if (is_array($response_body)) {
            $response[] = CodeEditor::make('response_body')
                ->wrap()
                ->language(Language::Json)
                ->formatStateUsing(fn () => Json::encode($response_body, JSON_PRETTY_PRINT))
                ->label('Response Body');
        } else {
            $response[] = CodeEditor::make('response_body')
                ->wrap()
                ->label('Response Body');
        }

        return $schema
            ->columns(1)
            ->schema([
                Section::make()
                    ->columns(1)
                    ->schema([
                        TextInput::make('url')
                            ->label('URL')
                            ->columnSpan(2)
                            ->prefix(fn (?RequestLog $record): ?string => $record->method?->getLabel())
                            ->prefixIcon(fn (?RequestLog $record): ?string => $record->type?->getIcon())
                            ->prefixIconColor(fn (?RequestLog $record): ?string => $record->type?->getColor()),
                    ]),
                Section::make()
                    ->columns(3)
                    ->schema([
                        TextInput::make('created_at')
                            ->label('Created'),
                        TextInput::make('time')
                            ->label('Time')
                            ->suffix('s'),
                        TextInput::make('status_code')
                            ->label('Status Code')
                            ->prefixIcon(fn (?RequestLog $record): string => match (true) {
                                ($record?->status_code ?? 0) >= 200 && ($record?->status_code ?? 0) < 300 => 'heroicon-o-check-circle',
                                ($record?->status_code ?? 0) <= 0 || ($record?->status_code ?? 0) > 299 => 'heroicon-o-exclamation-circle',
                                default => 'heroicon-o-question-mark-circle',
                            })
                            ->prefixIconColor(fn (?RequestLog $record): string => match (true) {
                                ($record?->status_code ?? 0) >= 200 && ($record?->status_code ?? 0) < 300 => 'success',
                                ($record?->status_code ?? 0) <= 0 || ($record?->status_code ?? 0) > 399 => 'danger',
                                ($record?->status_code ?? 0) > 299 => 'warning',
                                default => 'primary',
                            }),
                    ]),
                Section::make('Request')
                    ->columns(count($request))
                    ->schema($request),
                Section::make('Response')
                    ->columns(count($response))
                    ->schema($response),
            ]);
    }
}
