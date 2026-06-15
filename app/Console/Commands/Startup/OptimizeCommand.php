<?php

namespace App\Console\Commands\Startup;

use Illuminate\Console\Command;
use JetBrains\PhpStorm\NoReturn;
use Illuminate\Support\Facades\Artisan;

class OptimizeCommand extends Command
{
    protected $signature = 'startup:optimize {--clear} {--force}';

    protected $description = 'Управление оптимизацией приложения';

    #[NoReturn]
    public function handle(): void
    {
        $clear = (bool)$this->option('clear');
        $force = (bool)$this->option('force');
        if (!$clear) {
            if (app()->isProduction() || $force) {
                Artisan::call('config:cache');
                Artisan::call('route:cache');
                Artisan::call('event:cache');
                Artisan::call('icons:cache');
                Artisan::call('structure-scouts:cache');
                Artisan::call('view:cache');
                Artisan::call('optimize');
                Artisan::call('filament:optimize');
                Artisan::call('data:cache-structures');
                $this->info('Кеш создан', 'comment');
//                Artisan::call('octane:reload');
            } else {
                $this->error("Это не прод!, используй '--force'", 'warning');
            }
        } else if (!app()->isProduction() || $force) {
            cache()->clear();
            Artisan::call('cache:clear');
            Artisan::call('schedule:clear-cache');
            Artisan::call('optimize:clear');
            Artisan::call('filament:optimize-clear');
            $this->info('Кеш очищен', 'info');
//            Artisan::call('octane:reload');
        } else {
            $this->error("Это прод!, используй '--force'", 'warning');
        }
        exit(0);
    }
}
