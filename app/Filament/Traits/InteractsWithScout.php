<?php

namespace App\Filament\Traits;

use App\Helpers\ScoutSearchHelper;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords;

/**
 * @mixin ListRecords
 */
trait InteractsWithScout
{
    protected function applySearchToTableQuery(Builder $query): Builder
    {
        return ScoutSearchHelper::apply(
            query: $query,
            search: $this->getTableSearch(),
            fallback: function (Builder $q) {
                $this->applyColumnSearchesToTableQuery($q);
                $this->applyGlobalSearchToTableQuery($q);
                return $q;
            },
            logging: true,
        );
    }
}
