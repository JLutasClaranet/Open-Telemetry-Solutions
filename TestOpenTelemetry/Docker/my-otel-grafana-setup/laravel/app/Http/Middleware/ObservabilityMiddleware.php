<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\Context;
use OpenTelemetry\API\Globals;
use Illuminate\Support\Facades\Log;

class ObservabilityMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);
        $tracer = Globals::tracerProvider()->getTracer('laravel');

        $span = $tracer->spanBuilder($request->path())
            ->setSpanKind(SpanKind::KIND_SERVER)
            ->startSpan();

        $scope = $span->activate();

        $span->setAttribute('http.method', $request->method());
        $span->setAttribute('http.route', $request->path());
        $span->setAttribute('organization.name', 'XPTO Corp');

        $response = $next($request);

        $latency = round((microtime(true) - $start) * 1000, 2);
        $span->setAttribute('http.status_code', $response->status());
        $span->setAttribute('http.server.request.duration', $latency);

        $span->end();
        $scope->detach();

        Log::info('HTTP request completed', [
            'trace_id' => $span->getContext()->getTraceId(),
            'http.method' => $request->method(),
            'http.route' => $request->path(),
            'http.status_code' => $response->status(),
            'duration_ms' => $latency,
            'remote_ip' => $request->ip(),
            'organization.name' => 'XPTO Corp'
        ]);

        return $response;
    }
}
