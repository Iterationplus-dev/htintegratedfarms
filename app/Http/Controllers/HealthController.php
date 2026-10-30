<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Deployment health check: verifies the database and cache are reachable.
 * Returns only pass/fail so it leaks no infrastructure detail.
 */
class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->check(fn () => DB::connection()->getPdo() && DB::select('select 1')),
            'cache' => $this->check(function (): bool {
                Cache::put('health-check', 'ok', 10);

                return Cache::get('health-check') === 'ok';
            }),
        ];

        $healthy = ! in_array(false, $checks, true);

        return response()->json(
            ['status' => $healthy ? 'ok' : 'degraded', 'checks' => array_map(fn (bool $ok): string => $ok ? 'ok' : 'fail', $checks)],
            $healthy ? 200 : 503,
            ['Cache-Control' => 'no-store'],
        );
    }

    private function check(callable $probe): bool
    {
        try {
            return (bool) $probe();
        } catch (Throwable $exception) {
            Log::error('Health check failed.', ['error' => $exception->getMessage()]);

            return false;
        }
    }
}
