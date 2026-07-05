<?php

namespace App\Services;

use App\Models\Mod;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class FetchFullInfoService
{
    public function run(bool $force, ?int $limit, ?Closure $onProgress = null): array
    {
        $cooldownDays = config('factorio.full_info_cooldown_days', 7);
        $batchSize = config('factorio.full_info_batch_size', 50);
        $delayMs = config('factorio.full_info_delay_ms', 200);
        $maxRequests = config('factorio.full_info_max_requests', 1000);

        $effectiveLimit = $limit ?? $maxRequests;

        $processed = 0;
        $success = 0;
        $failed = 0;

        // Priority pass: process pending mods first (new or updated versions)
        $pendingQuery = Mod::where('pending_full_info', true)->orderBy('popularity', 'desc');
        $pendingTotal = min($pendingQuery->count(), $effectiveLimit);

        if ($pendingTotal > 0) {
            Log::info("FetchFullInfoService: найдено {$pendingTotal} модов в очереди обновления.");
            $pendingQuery->limit($pendingTotal);

            $pendingQuery->chunk($batchSize, function ($mods) use (
                &$processed,
                &$success,
                &$failed,
                $effectiveLimit,
                $delayMs,
                $onProgress,
            ) {
                foreach ($mods as $mod) {
                    if ($processed >= $effectiveLimit) {
                        return false;
                    }

                    $ok = $this->processMod($mod);
                    $ok ? $success++ : $failed++;
                    $processed++;

                    $onProgress && $onProgress($processed, $effectiveLimit);

                    if ($processed < $effectiveLimit) {
                        usleep($delayMs * 1000);
                    }
                }

                return true;
            });
        }

        // Regular pass: fill remaining budget with cooldown-based mods
        if (! $force && $processed < $effectiveLimit) {
            $remaining = $effectiveLimit - $processed;
            $cooldown = now()->subDays($cooldownDays);
            $query = Mod::query()
                ->where('pending_full_info', false)
                ->orderBy('popularity', 'desc')
                ->where(function ($q) use ($cooldown) {
                    $q->whereNull('fetch_full_info_at')
                        ->orWhere('fetch_full_info_at', '<', $cooldown);
                })
                ->whereNull('fetch_full_info_error')
                ->limit($remaining);

            $regularTotal = $query->count();
            if ($regularTotal > 0) {
                Log::info("FetchFullInfoService: ещё {$regularTotal} модов по cooldown.");
                $query->chunk($batchSize, function ($mods) use (
                    &$processed,
                    &$success,
                    &$failed,
                    $effectiveLimit,
                    $delayMs,
                    $onProgress,
                ) {
                    foreach ($mods as $mod) {
                        if ($processed >= $effectiveLimit) {
                            return false;
                        }

                        $ok = $this->processMod($mod);
                        $ok ? $success++ : $failed++;
                        $processed++;

                        $onProgress && $onProgress($processed, $effectiveLimit);

                        if ($processed < $effectiveLimit) {
                            usleep($delayMs * 1000);
                        }
                    }

                    return true;
                });
            }
        }

        // Force pass: when --force, process ALL mods regardless of cooldown
        if ($force && $processed < $effectiveLimit) {
            $remaining = $effectiveLimit - $processed;
            $query = Mod::query()
                ->where('pending_full_info', false)
                ->orderBy('popularity', 'desc')
                ->limit($remaining);

            $forceTotal = $query->count();
            if ($forceTotal > 0) {
                Log::info("FetchFullInfoService: force — ещё {$forceTotal} модов.");
                $query->chunk($batchSize, function ($mods) use (
                    &$processed,
                    &$success,
                    &$failed,
                    $effectiveLimit,
                    $delayMs,
                    $onProgress,
                ) {
                    foreach ($mods as $mod) {
                        if ($processed >= $effectiveLimit) {
                            return false;
                        }

                        $ok = $this->processMod($mod);
                        $ok ? $success++ : $failed++;
                        $processed++;

                        $onProgress && $onProgress($processed, $effectiveLimit);

                        if ($processed < $effectiveLimit) {
                            usleep($delayMs * 1000);
                        }
                    }

                    return true;
                });
            }
        }

        Log::info("FetchFullInfoService завершён: обновлено {$success}, ошибок {$failed}, всего {$processed}.");

        return [
            'processed' => $processed,
            'success' => $success,
            'failed' => $failed,
        ];
    }

    public function processMod(Mod $mod): bool
    {
        try {
            $ok = $mod->fetchFullInfo();
        } catch (ConnectionException $e) {
            $mod->update(['fetch_full_info_error' => $e->getMessage()]);
            $ok = false;
        } catch (Throwable $e) {
            Log::error("FetchFullInfoService: ошибка для {$mod->name}", [
                'exception' => $e,
            ]);
            $mod->update(['fetch_full_info_error' => $e->getMessage()]);
            $ok = false;
        }

        if ($ok) {
            $mod->update(['pending_full_info' => false]);
        }

        return $ok;
    }

    public function getErroredMods(): Collection
    {
        return Mod::whereNotNull('fetch_full_info_error')
            ->select('name', 'fetch_full_info_error', 'fetch_full_info_at')
            ->orderBy('fetch_full_info_error')
            ->get();
    }
}
