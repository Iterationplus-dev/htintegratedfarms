<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Scheduled tasks (run by `php artisan schedule:run` every minute — see deploy/systemd)
|--------------------------------------------------------------------------
*/

// Remove expired "Save and continue later" quote drafts.
Schedule::command('model:prune')->daily()->at('02:15')->onOneServer()->withoutOverlapping();

// Housekeeping for queues and caches.
Schedule::command('queue:prune-failed --hours=720')->daily()->at('02:30')->onOneServer();
Schedule::command('queue:prune-batches --hours=168')->daily()->at('02:35')->onOneServer();

// Remove orphaned media files and conversions that are no longer referenced.
Schedule::command('media-library:clean --force')->weekly()->sundays()->at('03:00')->onOneServer()->withoutOverlapping();

// Let the scheduler heartbeat be checked from monitoring (storage/logs/scheduler-heartbeat).
Schedule::call(fn () => file_put_contents(storage_path('logs/scheduler-heartbeat'), now()->toIso8601String()))
    ->everyFiveMinutes()
    ->name('scheduler-heartbeat');
