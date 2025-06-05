<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use OpenTelemetry\API\Globals;
use Illuminate\Support\Facades\Log;
use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\HistogramInterface;

//logs
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\SDK\Common\Attribute\Attributes;


class ObservabilityController extends BaseController
{

    private static ?CounterInterface $user_Info_Counter = null;
    private static ?HistogramInterface $process_duration_Histogram = null;

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
        $start = microtime(true);
        $tracer = Globals::tracerProvider()->getTracer('laravel');
        $meter = Globals::meterProvider()->getMeter('laravel');

        $span = $tracer->spanBuilder('manual-context-span')->startSpan();
        $scope = $span->activate();

        $span->setAttribute('user.id', '12345');
        $span->setAttribute('session.id', 'abcde');

        // Create or get Prometheus counter
        if (is_null(self::$user_Info_Counter)) {
            self::$user_Info_Counter = $meter->createCounter('user_info_total');
        }
    
        self::$user_Info_Counter->add(1, [
            'method' => $request->method(),
            'route' => $request->path(),
            'user_name' => "Jorge",
            'user_location' => "Portugal",
            'organization' => config('observability.organization_name')
        ]);

        Log::info('Manual span with custom context triggered', [
            'endpoint' => '/trigger-context',
            'organization.name' => config('observability.organization_name')
        ]);

        if (is_null(self::$process_duration_Histogram)) {
            self::$process_duration_Histogram = $meter->createHistogram('process_duration_ms');
        }

        // Simulated logic
        usleep(500000); // 50ms
        $duration = (microtime(true) - $start) * 1000;

        self::$process_duration_Histogram->record($duration, [
            'method' => $request->method(),
            'route' => $request->path(),
            'operation' => "Email-Generator",
            'status' => "fail",
            'organization' => config('observability.organization_name')
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

        // Create or get Prometheus counter
        if (is_null(self::$user_Info_Counter)) {
            self::$user_Info_Counter = $meter->createCounter('user_info_total');
        }
    
        self::$user_Info_Counter->add(1, [
            'method' => $request->method(),
            'route' => $request->path(),
            'user_name' => "Miguel",
            'user_location' => "Espanha",
            'organization' => config('observability.organization_name')
        ]);

      
        

        // Simulated logic
        usleep(50000); // 50ms

        $duration = (microtime(true) - $start) * 1000;

        if (is_null(self::$process_duration_Histogram)) {
            self::$process_duration_Histogram = $meter->createHistogram('process_duration_ms');
        }

        self::$process_duration_Histogram->record($duration, [
            'method' => $request->method(),
            'route' => $request->path(),
            'operation' => "Email-Generator",
            'status' => "success",
            'organization' => config('observability.organization_name')
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
