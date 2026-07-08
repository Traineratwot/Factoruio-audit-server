<?php

namespace App\Console\Commands\Scanner;

use App\Facades\AuditService;
use Illuminate\Console\Command;

class VersionCommand extends Command
{
    protected $signature = 'scanner:version';

    protected $description = 'Fetch and cache the current scanner version';

    public function handle(): int
    {
        $version = AuditService::cachedScannerVersion();

        if ($version === null) {
            $this->error('Failed to fetch scanner version. The scanner may be unavailable.');

            return self::FAILURE;
        }

        $this->info("Scanner version: {$version}");

        return self::SUCCESS;
    }
}
