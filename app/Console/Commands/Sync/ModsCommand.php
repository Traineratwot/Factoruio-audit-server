<?php

namespace App\Console\Commands\Sync;

use App\Facades\FactorioService;
use App\Models\Mod;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;

class ModsCommand extends Command
{
    protected $signature = 'sync:mods';

    protected $description = 'Command description';

    /**
     * @throws ConnectionException
     */
    public function handle(): void
    {
        $data = FactorioService::mods();
        $this->withProgressBar($data, function (array $item) {
            Mod::updateOrCreate([
                "name" => $item['name']
            ], [
                "owner" => $item['owner'],
                "latest_version" => $item['latest_release']['version'] ?? null,
                "category" => $item['category'],
                "title" => $item['title'],
                "summary" => $item['summary'],
                "downloads_count" => $item['downloads_count'],
                "popularity" => $item['score'],
            ]);
        });
    }
}
