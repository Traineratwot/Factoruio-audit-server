<?php

namespace App\Jobs;

use App\Services\ModsSyncService;
use Croustibat\FilamentJobsMonitor\Traits\QueueProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncModsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, QueueProgress, SerializesModels;

    public int $timeout = 600;

    public function __construct()
    {
        $this->onQueue('work2');
    }

    /**
     * @throws ConnectionException
     */
    public function handle(ModsSyncService $service): void
    {
        $result = $service->run(
            onProgress: function ($processed, $total) {
                $this->setProgress((int) ($processed / $total * 100));
            },
        );

        Log::info("SyncModsJob завершён: синхронизировано {$result['total']} модов, помечено на обновление: {$result['pending']}.");
    }
}
