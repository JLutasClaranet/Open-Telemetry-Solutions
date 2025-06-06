<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\HistogramInterface;
use Illuminate\Support\Facades\Log;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\SDK\Common\Attribute\Attributes;

class ObservabilityMiddleware
{

    private static ?CounterInterface $httpRequestCounter = null;
    private static ?HistogramInterface $httpDurationHistogram = null;
    

    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);
        $tracer = Globals::tracerProvider()->getTracer('laravel');
        $meter = Globals::meterProvider()->getMeter('laravel');
        $logger = Globals::loggerProvider()->getLogger('laravel');

        

        $span = $tracer->spanBuilder($request->path())
            ->setSpanKind(SpanKind::KIND_SERVER)
            ->startSpan();

        $scope = $span->activate();

         // Add standard semantic attributes
        $span->setAttribute('http.request.method', $request->method());
        $span->setAttribute('url.full', $request->fullUrl());
        $span->setAttribute('server.address', $request->server('SERVER_NAME'));
        $span->setAttribute('server.port', $request->getPort());
        $span->setAttribute('user_agent.original', $request->userAgent());
        $span->setAttribute('network.peer.address', $request->ip());
        $span->setAttribute('organization.name', config('observability.organization_name'));

        $response = $next($request);

        $latency = round((microtime(true) - $start) * 1000, 2);
        $span->setAttribute('http.status_code', $response->status());
        $span->setAttribute('http.server.request.duration', $latency);

        $span->end();
        $scope->detach();

        // Create or get Prometheus counter
        
        if (is_null(self::$httpRequestCounter)) {
            self::$httpRequestCounter = $meter->createCounter('http_requests_total');
        }

        self::$httpRequestCounter->add(1, [
            'method' => $request->method(),
            'route' => $request->path(),
            'status_code' => $response->status(),
            'organization' => config('observability.organization_name')
        ]);

        $duration = (microtime(true) - $start) * 1000;

       
        if (is_null(self::$httpDurationHistogram)) {
            self::$httpDurationHistogram = $meter->createHistogram('http_request_duration_ms');
        }

        self::$httpDurationHistogram->record($duration, [
            'method' => $request->method(),
            'route' => $request->path(),
            'status_code' => $response->status(),
            'organization' => config('observability.organization_name')
        ]);


        //Logs to elastic via collector
        
        $logRecord = (new LogRecord('HTTP request completed'))
        ->setAttributes(Attributes::create([
            'trace_id' => $span->getContext()->getTraceId(),
            'span_id' => $span->getContext()->getSpanId(),
            'http.method' => $request->method(),
            'http.route' => $request->path(),
            'url.full' => $request->fullUrl(),
            'http.status_code' => $response->status(),
            'http.user_agent' => $request->userAgent(),
            'duration_ms' => $latency,
            'remote_ip' => $request->ip(),
            'organization.name' => config('observability.organization_name'),
            'app.environment' => app()->environment()
        ]));

        $logger->emit($logRecord);


        Log::info('HTTP request completed', [
            'trace_id' => $span->getContext()->getTraceId(),
            'http.method' => $request->method(),
            'http.route' => $request->path(),
            'http.status_code' => $response->status(),
            'duration_ms' => $latency,
            'remote_ip' => $request->ip(),
            'organization.name' => config('observability.organization_name')
        ]);

        return $response;
    }
}
