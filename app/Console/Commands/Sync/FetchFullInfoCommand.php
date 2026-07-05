<?php

namespace App\Console\Commands\Sync;

use App\Jobs\FetchFullInfoJob;
use App\Services\FetchFullInfoService;
use Illuminate\Console\Command;

class FetchFullInfoCommand extends Command
{
    protected $signature = 'sync:full-info
        {--force : Игнорировать cooldown и ошибки, обновить все моды}
        {--limit= : Максимальное количество модов для обработки}
        {--errors : Показать моды с ошибками}
        {--job : Диспатчить в очередь вместо выполнения}';

    protected $description = 'Обновление полной информации по модам (fetchFullInfo)';

    public function handle(FetchFullInfoService $service): int
    {
        if ($this->option('errors')) {
            $this->showErroredMods($service);

            return self::SUCCESS;
        }

        if ($this->option('job')) {
            FetchFullInfoJob::dispatch(
                force: $this->option('force'),
                limit: $this->option('limit') ? (int) $this->option('limit') : null,
            );

            $this->info('FetchFullInfoJob отправлен в очередь.');

            return self::SUCCESS;
        }

        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $force = $this->option('force');

        $cooldownDays = config('factorio.full_info_cooldown_days', 7);
        $maxRequests = config('factorio.full_info_max_requests', 1000);
        $total = min($limit ?? $maxRequests, $maxRequests);

        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        $result = $service->run(
            force: $force,
            limit: $limit,
            onProgress: function () use ($progressBar) {
                $progressBar->advance();
            },
        );

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Обновлено: {$result['success']}, ошибок: {$result['failed']}, всего: {$result['processed']}");

        $erroredCount = $service->getErroredMods()->count();
        if ($erroredCount > 0) {
            $this->warn("Модов с ошибками в базе: {$erroredCount}. Используйте sync:full-info --errors для просмотра.");
        }

        return self::SUCCESS;
    }

    private function showErroredMods(FetchFullInfoService $service): void
    {
        $mods = $service->getErroredMods();

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
