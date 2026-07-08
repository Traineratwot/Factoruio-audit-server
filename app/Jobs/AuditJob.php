<?php

namespace App\Jobs;

use App\Events\AuditCompleted;
use App\Models\Mod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class AuditJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $modId,
        public ?string $version = null,
        public ?string $auditToken = null,
    ) {
        $this->onQueue('work1');
    }

    public function handle(): void
    {
        $mod = Mod::findOrFail($this->modId);
        $version = $this->version ?? $mod->latest_version;

        $reportUrl = null;
        $error = null;

        try {
            $report = $mod->runAudit($version);
            if ($report) {
                $reportUrl = '/report/mod/'.$mod->id.'/version/'.$version;
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }

        if ($this->auditToken) {
            broadcast(new AuditCompleted(
                auditToken: $this->auditToken,
                modId: $mod->id,
                modName: $mod->name,
                version: $version,
                reportUrl: $reportUrl,
                error: $error,
            ));
        }
    }
}
