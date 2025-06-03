<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use OpenTelemetry\API\Globals;
use Illuminate\Support\Facades\Log;

//logs
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\SDK\Common\Attribute\Attributes;


class ObservabilityController extends BaseController
{
    public function root(Request $request)
    {
        Log::info('Root endpoint accessed', [
            'endpoint' => '/',
            'http.method' => $request->method(),
            'organization.name' => config('observability.organization_name')
        ]);
        return response()->json(['message' => 'Hello from Laravel']);
    }

    public function trigger(Request $request)
    {
        $logger = Globals::loggerProvider()->getLogger('laravel');
        $tracer = Globals::tracerProvider()->getTracer('laravel');
        $span = $tracer->spanBuilder('manual-span')->startSpan();
        $scope = $span->activate();

        $logRecord = (new LogRecord('trigger check accessed'))
        ->setAttributes(Attributes::create([
            'endpoint' => '/trigger',
            'organization.name' => config('observability.organization_name'),
            'route.objective' => 'User Sign In',
          
        ]));

        $logger->emit($logRecord);

        Log::info('Triggered manual span', [
            'endpoint' => '/trigger',
            'organization.name' => config('observability.organization_name')
        ]);

        $span->end();
        $scope->detach();

        return response()->json(['message' => 'Manual trace span sent to the collector']);
    }

    public function triggerContext(Request $request)
    {
        $tracer = Globals::tracerProvider()->getTracer('laravel');
        $meter = Globals::meterProvider()->getMeter('laravel');

        $span = $tracer->spanBuilder('manual-context-span')->startSpan();
        $scope = $span->activate();

        $span->setAttribute('user.id', '12345');
        $span->setAttribute('session.id', 'abcde');

            // Create or get Prometheus counter
            $counter = $meter->createCounter('user_logged');
            $counter->add(1, [
                'user.id' => 'Jorge Andrade',
                'session.id' => '11111'
            ]);

            $counter->add(1, [
                    'user.id' => 'Miguel Barbosa',
                    'session.id' => '22222'
            ]);

            $counter->add(1, [
                'user.id' => 'Filipe Santos',
                'session.id' => '33333'
            ]);

            $counter->add(1, [
                'user.id' => 'Filipe Santos',
                'session.id' => '33333'
            ]);

        Log::info('Manual span with custom context triggered', [
            'endpoint' => '/trigger-context',
            'organization.name' => config('observability.organization_name')
        ]);

        $span->end();
        $scope->detach();

        return response()->json(['message' => 'Manual trace span with custom context attributes sent']);
    }

    public function health(Request $request)
    {
        $start = microtime(true);
        $logger = Globals::loggerProvider()->getLogger('laravel');
        $meter = Globals::meterProvider()->getMeter('laravel');

        // Track hits to this endpoint
        $counter = $meter->createCounter('health_trigger_hits_total');
        $counter->add(1, [
            'endpoint' => '/health',
            'organization.name' => config('observability.organization_name'),
        ]);

        $histogram = $meter->createHistogram('custom_logic_health_duration_ms');
        

        // Simulated logic
        usleep(50000); // 50ms

        $duration = (microtime(true) - $start) * 1000;
        $histogram->record($duration, [
            'operation' => 'simulate_logic_time_health',
            'organization.name' => config('observability.organization_name'),
        ]);

        $logRecord = (new LogRecord('Health check accessed'))
        ->setAttributes(Attributes::create([
            'endpoint' => '/health',
            'organization.name' => config('observability.organization_name')
          
        ]));

        $logger->emit($logRecord);

        Log::info('Health check accessed', [
            'endpoint' => '/health',
            'organization.name' => config('observability.organization_name')
        ]);

        return response()->json(['status' => 'healthy']);
    }
}
