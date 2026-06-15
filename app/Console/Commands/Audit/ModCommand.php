<?php

namespace App\Console\Commands\Audit;

use App\Models\Mod;
use App\Models\Report;
use Illuminate\Console\Command;
use Tivoka\Client;

class ModCommand extends Command
{
    protected $signature = 'audit:mod {mod}';

    protected $description = 'test';

    public function handle()
    {
        $name= $this->argument('mod');
        Mod::whereName($name)->firstOrFail()->runAudit();
    }
}
