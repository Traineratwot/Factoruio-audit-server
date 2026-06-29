<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Full info cooldown
    |--------------------------------------------------------------------------
    |
    | How many days to wait before re-fetching full mod info from the API.
    | Default: 7 days.
    |
    */
    'full_info_cooldown_days' => (int) env('FACTORIO_FULL_INFO_COOLDOWN_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Batch size
    |--------------------------------------------------------------------------
    |
    | Number of mods to process per batch. Each batch has a sleep delay
    | between requests to avoid hitting rate limits.
    |
    */
    'full_info_batch_size' => (int) env('FACTORIO_FULL_INFO_BATCH_SIZE', 50),

    /*
    |--------------------------------------------------------------------------
    | Delay between requests (ms)
    |--------------------------------------------------------------------------
    |
    | Milliseconds to sleep between each API request to avoid rate limiting.
    |
    */
    'full_info_delay_ms' => (int) env('FACTORIO_FULL_INFO_DELAY_MS', 200),

    /*
    |--------------------------------------------------------------------------
    | Max requests per run
    |--------------------------------------------------------------------------
    |
    | Hard limit on the number of API requests per command run.
    |
    */
    'full_info_max_requests' => (int) env('FACTORIO_FULL_INFO_MAX_REQUESTS', 1000),
];
