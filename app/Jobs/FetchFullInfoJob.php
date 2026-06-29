<?php

namespace App\Jobs;

use App\Models\Mod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class FetchFullInfoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public function __construct(
        public bool $force = false,
        public ?int $limit = null,
    ) {
        $this->onQueue('work1');
    }

    public function handle(): void
    {
        $cooldownDays = config('factorio.full_info_cooldown_days', 7);
        $batchSize = config('factorio.full_info_batch_size', 50);
        $delayMs = config('factorio.full_info_delay_ms', 200);
        $maxRequests = config('factorio.full_info_max_requests', 1000);

        $query = Mod::query()->orderBy('popularity', 'desc');

        if (! $this->force) {
            $cooldown = now()->subDays($cooldownDays);
            $query->where(function ($q) use ($cooldown) {
                $q->whereNull('fetch_full_info_at')
                    ->orWhere('fetch_full_info_at', '<', $cooldown);
            });
            $query->whereNull('fetch_full_info_error');
        }

        $allMatching = $query->count();

        if ($allMatching === 0) {
            Log::info('FetchFullInfoJob: нет модов для обновления.');

            return;
        }

        $total = min($allMatching, $maxRequests);
        if ($this->limit !== null) {
            $total = min($total, $this->limit);
            $query->limit($total);
        }

        Log::info("FetchFullInfoJob: найдено {$allMatching} модов, будет обработано {$total}.");

        $processed = 0;
        $success = 0;
        $failed = 0;

        $query->chunk($batchSize, function ($mods) use (
            &$processed,
            &$success,
            &$failed,
            $maxRequests,
            $delayMs,
        ) {
            foreach ($mods as $mod) {
                if ($processed >= $maxRequests) {
                    return false;
                }

                try {
                    $ok = $mod->fetchFullInfo();
                } catch (ConnectionException $e) {
                    $mod->update(['fetch_full_info_error' => $e->getMessage()]);
                    $ok = false;
                } catch (Throwable $e) {
                    Log::error("FetchFullInfoJob: ошибка для {$mod->name}", [
                        'exception' => $e,
                    ]);
                    $mod->update(['fetch_full_info_error' => $e->getMessage()]);
                    $ok = false;
                }

                $ok ? $success++ : $failed++;
                $processed++;

                if ($processed < $maxRequests) {
                    usleep($delayMs * 1000);
                }
            }

            return true;
        });

        Log::info("FetchFullInfoJob завершён: обновлено {$success}, ошибок {$failed}, всего {$processed}.");
    }
}
