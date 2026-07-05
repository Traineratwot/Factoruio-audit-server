<?php

namespace App\Services;

use App\Facades\FactorioService;
use App\Models\Author;
use App\Models\Mod;
use Closure;
use Illuminate\Http\Client\ConnectionException;

class ModsSyncService
{
    /**
     * @return array{total: int, pending: int}
     *
     * @throws ConnectionException
     */
    public function run(?Closure $onProgress = null): array
    {
        $data = FactorioService::mods();
        $total = count($data);

        if ($total === 0) {
            return ['total' => 0, 'pending' => 0];
        }

        // Sync authors first
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
                    'factorio_version' => $item['latest_release']['info_json']['factorio_version'] ?? null,
                    'latest_release_date' => $item['latest_release']['released_at'] ?? null,
                    'category' => $item['category'],
                    'title' => $item['title'],
                    'summary' => $item['summary'],
                    'downloads_count' => $item['downloads_count'],
                    'popularity' => $item['score'],
                    'pending_full_info' => true,
                ];
            }

            // pending_full_info is NOT in the update columns list:
            // - INSERT: new rows get pending_full_info = true
            // - UPDATE: the PostgreSQL trigger sets pending_full_info = true when latest_version changes
            Mod::upsert($upsertData, ['name'], [
                'author_id', 'latest_version', 'category', 'title',
                'summary', 'downloads_count', 'popularity',
            ]);

            $processed += count($chunk);
            if ($onProgress) {
                $onProgress($processed, $total);
            }
        }

        $pending = Mod::where('pending_full_info', true)->count();

        return [
            'total' => $total,
            'pending' => $pending,
        ];
    }
}
