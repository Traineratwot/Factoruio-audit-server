<?php

namespace App\Console\Commands\Sync;

use App\Facades\FactorioService;
use App\Models\Mod;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;

class ModsCommand extends Command
{
    protected $signature = 'sync:mods';

    protected $description = 'Синхронизация модов из FactorioService';

    /**
     * @throws ConnectionException
     */
    public function handle(): void
    {
        $data = FactorioService::mods();
        $total = count($data);

        if ($total === 0) {
            $this->info('Нет данных для синхронизации.');
            return;
        }

        $this->info("Найдено {$total} модов. Начинаю синхронизацию...");

        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        // Разбиваем данные на пачки по 500 записей
        $chunks = $data->chunk(500);

        foreach ($chunks as $chunk) {
            $upsertData = [];
            foreach ($chunk as $item) {
                $upsertData[] = [
                    'name'             => $item['name'],
                    'owner'            => $item['owner'],
                    'latest_version'   => $item['latest_release']['version'] ?? null,
                    'category'         => $item['category'],
                    'title'            => $item['title'],
                    'summary'          => $item['summary'],
                    'downloads_count'  => $item['downloads_count'],
                    'popularity'       => $item['score'],
                ];
            }

            // Массовая вставка или обновление при конфликте по полю 'name'
            Mod::upsert($upsertData, ['name'], [
                'owner', 'latest_version', 'category', 'title',
                'summary', 'downloads_count', 'popularity'
            ]);

            $progressBar->advance(count($chunk));
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info('Синхронизация завершена.');
    }
}
