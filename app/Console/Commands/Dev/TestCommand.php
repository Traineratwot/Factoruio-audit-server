<?php

namespace App\Console\Commands\Dev;

use App\Facades\AuditService;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'dev:test';

    protected $description = 'Command description';

    public function handle(): void
    {
        dd(AuditService::scannerVersion());
    }
}
