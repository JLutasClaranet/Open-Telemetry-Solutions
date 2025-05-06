<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ObservabilityMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $start = hrtime(true);
        $response = $next($request);
        $end = hrtime(true);
        $latencyMs = ($end - $start) / 1e6;

        $span = app(TracerProviderInterface::class)->getTracer('laravel')->spanBuilder($request->path())->startSpan();
        $span->setAttribute('http.method', $request->method());
        $span->setAttribute('http.route', $request->path());
        $span->setAttribute('http.status_code', $response->status());
        $span->end();

        Log::info('Request completed', [
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->status(),
            'duration_ms' => round($latencyMs, 2),
            'ip' => $request->ip()
        ]);

        return $response;
    }
}
