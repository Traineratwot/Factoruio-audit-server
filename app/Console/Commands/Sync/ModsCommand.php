<?php

namespace App\Console\Commands\Sync;

use App\Services\ModsSyncService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;

class ModsCommand extends Command
{
    protected $signature = 'sync:mods';

    protected $description = 'Синхронизация модов из FactorioService';

    /**
     * @throws ConnectionException
     */
    public function handle(ModsSyncService $service): void
    {
        $this->info('Начинаю синхронизацию...');

        $progressBar = $this->output->createProgressBar(100);
        $progressBar->start();

        $result = $service->run(
            onProgress: function ($processed, $total) use ($progressBar) {
                $progressBar->setMaxSteps($total);
                $progressBar->setProgress($processed);
            },
        );

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Синхронизация завершена. Модов: {$result['total']}, помечено на обновление: {$result['pending']}.");
    }
}
