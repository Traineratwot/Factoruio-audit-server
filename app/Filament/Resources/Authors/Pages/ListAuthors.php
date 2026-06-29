<?php

namespace App\Filament\Resources\Authors\Pages;

use App\Filament\Resources\Authors\AuthorResource;
use App\Filament\Traits\InteractsWithScout;
use Filament\Resources\Pages\ListRecords;

class ListAuthors extends ListRecords
{
    use InteractsWithScout;

    protected static string $resource = AuthorResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
