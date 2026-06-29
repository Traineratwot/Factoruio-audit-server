<?php

namespace App\Jobs;

use App\Facades\FactorioService;
use App\Models\Author;
use App\Models\Mod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncModsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public function __construct()
    {
    }

    /**
     * @throws ConnectionException
     */
    public function handle(): void
    {
        $data = FactorioService::mods();
        $total = count($data);

        if ($total === 0) {
            Log::info('SyncModsJob: нет данных для синхронизации.');

            return;
        }

        Log::info("SyncModsJob: найдено {$total} модов.");

        $ownerNames = $data->pluck('owner')->unique()->filter()->values();
        foreach ($ownerNames as $name) {
            Author::firstOrCreate(['name' => $name]);
        }

        $authorsByName = Author::pluck('id', 'name');

        $chunks = $data->chunk(500);
        $processed = 0;

        foreach ($chunks as $chunk) {
            $upsertData = [];
            foreach ($chunk as $item) {
                $upsertData[] = [
                    'name' => $item['name'],
                    'author_id' => $authorsByName[$item['owner']] ?? null,
                    'latest_version' => $item['latest_release']['version'] ?? null,
                    'category' => $item['category'],
                    'title' => $item['title'],
                    'summary' => $item['summary'],
                    'downloads_count' => $item['downloads_count'],
                    'popularity' => $item['score'],
                ];
            }

            Mod::upsert($upsertData, ['name'], [
                'author_id', 'latest_version', 'category', 'title',
                'summary', 'downloads_count', 'popularity',
            ]);

            $processed += count($chunk);
        }

        Log::info("SyncModsJob завершён: синхронизировано {$processed} модов.");
    }
}
