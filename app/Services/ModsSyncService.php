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
        $pendingIds = [];

        foreach ($chunks as $chunk) {
            $names = $chunk->pluck('name');
            $existingVersions = Mod::whereIn('name', $names)
                ->pluck('latest_version', 'name');

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

            $upsertedMods = Mod::whereIn('name', $names)->get();
            foreach ($upsertedMods as $mod) {
                $oldVersion = $existingVersions->get($mod->name);
                $newVersion = $chunk->firstWhere('name', $mod->name)['latest_release']['version'] ?? null;

                if ($newVersion && ($oldVersion === null || $newVersion !== $oldVersion)) {
                    $pendingIds[] = $mod->id;
                }
            }

            $processed += count($chunk);
            if ($onProgress) {
                $onProgress($processed, $total);
            }
        }

        if ($pendingIds !== []) {
            Mod::whereIn('id', $pendingIds)
                ->where('pending_full_info', false)
                ->update(['pending_full_info' => true]);
        }

        return [
            'total' => $total,
            'pending' => count($pendingIds),
        ];
    }
}
