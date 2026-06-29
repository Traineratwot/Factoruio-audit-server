<?php

namespace App\Console\Commands\Audit;

use App\Models\Mod;
use Illuminate\Console\Command;

class ModCommand extends Command
{
    protected $signature = 'audit:mod {mod}';

    protected $description = 'test';

    public function handle()
    {
        $name = $this->argument('mod');
        Mod::whereName($name)->firstOrFail()->runAudit();
    }
}
