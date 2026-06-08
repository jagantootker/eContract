<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Schedule — eKontrak API
|--------------------------------------------------------------------------
| Laravel 12 uses routes/console.php for scheduling (no Kernel.php).
*/

// Run contract expiry check daily at 8:00 AM
Schedule::command('ekontrak:check-expiry')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/ekontrak-expiry.log'));
