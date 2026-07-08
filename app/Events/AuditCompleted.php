<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuditCompleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $queue = 'notifications';

    public function __construct(
        public string $auditToken,
        public int $modId,
        public string $modName,
        public string $version,
        public ?string $reportUrl = null,
        public ?string $error = null,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel("audit.{$this->auditToken}");
    }

    public function broadcastAs(): string
    {
        return 'AuditCompleted';
    }

    public function broadcastWith(): array
    {
        return [
            'mod_id' => $this->modId,
            'mod_name' => $this->modName,
            'version' => $this->version,
            'report_url' => $this->reportUrl,
            'error' => $this->error,
        ];
    }
}
