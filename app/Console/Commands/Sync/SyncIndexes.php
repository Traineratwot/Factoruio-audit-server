<?php

namespace App\Console\Commands\Sync;

use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Console\Command;
use Laravel\Scout\Console\DeleteAllIndexesCommand;
use Laravel\Scout\Console\ImportCommand;
use Laravel\Scout\Console\SyncIndexSettingsCommand;

class SyncIndexes extends Command
{
    use Batchable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:index {--clear : Очистить перед синхронизацией} {--sync : Синхронный режим} {--model= : Опциональное имя модели для синхронизации}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Синхронизация индексов';

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle(): void
    {
        $clear = (bool)$this->option('clear');
        $sync = (bool)$this->option('sync');
        $modelOption = $this->option('model');

        if ($sync) {
            config(['scout.queue' => false]);
        }

        if (config('scout.queue') === false) {
            $this->question('Синхронный режим');
        }

        $data = config('scout.meilisearch.index-settings');
        $keys = array_keys($data);

        // Фильтруем модели, если указана опция --model
        if ($modelOption) {
            $keys = array_filter($keys, fn($key) => $key === $modelOption);
            if (empty($keys)) {
                $this->error("Модель '{$modelOption}' не найдена в конфигурации");
                return;
            }
        }

        if ($clear) {
            $this->call(DeleteAllIndexesCommand::class);
            $this->call(SyncIndexSettingsCommand::class);
            if (!$this->confirm('Продолжить?')) {
                return;
            }
        }

        foreach ($keys as $model) {
            try {
                $this->call(ImportCommand::class, ['model' => $model]);
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
        }

        $this->newLine();
    }
}
