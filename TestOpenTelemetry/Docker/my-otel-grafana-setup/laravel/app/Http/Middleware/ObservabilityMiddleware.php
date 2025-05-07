<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use OpenTelemetry\SDK\Trace\TracerProvider;  // Change to the concrete class
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ObservabilityMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Track start time
        $start = hrtime(true);
        
        // Proceed with the request
        $response = $next($request);
        
        // Track end time
        $end = hrtime(true);
        
        // Calculate latency in milliseconds
        $latencyMs = ($end - $start) / 1e6;

        // Get the TracerProvider instance
        $span = app(TracerProvider::class)  // Use the concrete class instead of the interface
            ->getTracer('laravel')
            ->spanBuilder($request->path())
            ->startSpan();

        // Set the span attributes for tracing
        $span->setAttribute('http.method', $request->method());
        $span->setAttribute('http.route', $request->path());
        $span->setAttribute('http.status_code', $response->status());
        
        // End the span
        $span->end();

        // Log request details
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
