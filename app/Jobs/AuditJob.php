<?php

namespace App\Jobs;

use App\Models\Mod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AuditJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $modId,
        public ?string $version = null
    ) {
        $this->onQueue('work1');
    }

    public function handle(): void
    {
        $mod = Mod::findOrFail($this->modId);
        $mod->runAudit($this->version);
    }
}
