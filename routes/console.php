<?php

use App\Console\Commands\Scanner\VersionCommand;
use App\Console\Commands\Sync\SyncIndexes;
use App\Jobs\FetchFullInfoJob;
use App\Jobs\SyncModsJob;
use Illuminate\Database\Console\PruneCommand;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new FetchFullInfoJob(limit: 10))->everyFiveMinutes();
Schedule::job(new SyncModsJob)->hourly();
Schedule::command(VersionCommand::class)->hourly();
Schedule::command(SyncIndexes::class)->daily();
Schedule::command(PruneCommand::class)->daily();
