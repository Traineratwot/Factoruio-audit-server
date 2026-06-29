<?php

namespace App\Console\Commands\Sync;

use App\Models\Mod;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Throwable;

class FetchFullInfoCommand extends Command
{
    protected $signature = 'sync:full-info
        {--force : Игнорировать cooldown и ошибки, обновить все моды}
        {--limit= : Максимальное количество модов для обработки}
        {--errors : Показать моды с ошибками}';

    protected $description = 'Обновление полной информации по модам (fetchFullInfo)';

    public function handle(): void
    {
        if ($this->option('errors')) {
            $this->showErroredMods();

            return;
        }

        $cooldownDays = config('factorio.full_info_cooldown_days', 7);
        $batchSize = config('factorio.full_info_batch_size', 50);
        $delayMs = config('factorio.full_info_delay_ms', 200);
        $maxRequests = config('factorio.full_info_max_requests', 1000);

        $query = Mod::query()->inRandomOrder();

        if (! $this->option('force')) {
            $cooldown = now()->subDays($cooldownDays);
            $query->where(function ($q) use ($cooldown) {
                $q->whereNull('fetch_full_info_at')
                    ->orWhere('fetch_full_info_at', '<', $cooldown);
            });

            $query->whereNull('fetch_full_info_error');
        }

        $allMatching = $query->count();

        if ($allMatching === 0) {
            $this->info('Нет модов для обновления.');

            return;
        }

        $total = min($allMatching, $maxRequests);
        if ($this->option('limit')) {
            $total = min($total, (int) $this->option('limit'));
            $query->limit($total);
        }

        $this->info("Найдено {$allMatching} модов для обновления (cooldown: {$cooldownDays} дн.).");
        if ($total < $allMatching) {
            $this->info("Будет обработано: {$total}");
        }

        $processed = 0;
        $success = 0;
        $failed = 0;

        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        $query->chunk($batchSize, function ($mods) use (
            &$processed,
            &$success,
            &$failed,
            $progressBar,
            $maxRequests,
            $delayMs,
        ) {
            foreach ($mods as $mod) {
                if ($processed >= $maxRequests) {
                    $this->newLine();
                    $this->warn("Достигнут лимит запросов ({$maxRequests}). Останавливаю.");

                    return false;
                }

                try {
                    $ok = $mod->fetchFullInfo();
                } catch (Throwable $e) {
                    $mod->update(['fetch_full_info_error' => $e->getMessage()]);
                    $ok = false;
                }

                if ($ok) {
                    $success++;
                } else {
                    $failed++;
                }

                $processed++;
                $progressBar->advance();

                if ($processed < $maxRequests) {
                    usleep($delayMs * 1000);
                }
            }

            return true;
        });

        $progressBar->finish();
        $this->newLine(2);

        $erroredCount = Mod::whereNotNull('fetch_full_info_error')->count();

        $this->info("Обновлено: {$success}, ошибок: {$failed}, всего: {$processed}");

        if ($erroredCount > 0) {
            $this->warn("Модов с ошибками в базе: {$erroredCount}. Используйте sync:full-info --errors для просмотра.");
        }
    }

    private function showErroredMods(): void
    {
        $mods = Mod::whereNotNull('fetch_full_info_error')
            ->select('name', 'fetch_full_info_error', 'fetch_full_info_at')
            ->orderBy('fetch_full_info_error')
            ->get();

        if ($mods->isEmpty()) {
            $this->info('Нет модов с ошибками.');

            return;
        }

        $this->info("Моды с ошибками ({$mods->count()}):");
        $this->newLine();

        $this->table(
            ['Name', 'Error', 'Last attempt'],
            $mods->map(fn ($m) => [
                $m->name,
                str($m->fetch_full_info_error)->limit(80),
                $m->fetch_full_info_at?->format('Y-m-d H:i') ?? '-',
            ])
        );
    }
}
