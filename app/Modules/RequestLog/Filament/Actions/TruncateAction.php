<?php

namespace App\Modules\RequestLog\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Query\Builder;

class TruncateAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->label('Clear');
        $this->color('danger');
        $this->icon(Heroicon::Trash);
        $this->requiresConfirmation();
        $this->action(function () {
            /** @var Builder $model */
            $model = $this->getModel();
            if ($model) {
                $model::truncate();
            }
        });
    }
}
