<?php

namespace App\Jobs;

use App\Services\FetchFullInfoService;
use Croustibat\FilamentJobsMonitor\Traits\QueueProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchFullInfoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, QueueProgress, SerializesModels;

    public int $timeout = 3600;

    public function __construct(
        public bool $force = false,
        public ?int $limit = null,
    ) {
        $this->onQueue('content');
    }

    public function handle(FetchFullInfoService $service): void
    {
        $service->run(
            force: $this->force,
            limit: $this->limit,
            onProgress: function ($processed, $total) {
                $this->setProgress((int) ($processed / $total * 100));
            },
        );
    }
}
