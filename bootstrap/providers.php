<?php

use App\Modules\RequestLog\RequestLogServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    RequestLogServiceProvider::class,
];
