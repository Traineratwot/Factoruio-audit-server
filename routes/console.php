<?php

use App\Jobs\FetchFullInfoJob;
use App\Jobs\SyncModsJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new FetchFullInfoJob(limit: 10))->everyFiveMinutes();
Schedule::job(new SyncModsJob)->hourly();
