<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use OpenTelemetry\API\Globals;
use Illuminate\Support\Facades\Log;

class ObservabilityController extends BaseController
{
    public function root(Request $request)
    {
        Log::info('Root endpoint accessed', [
            'endpoint' => '/',
            'http.method' => $request->method(),
            'organization.name' => 'XPTO Corp'
        ]);
        return response()->json(['message' => 'Hello from Laravel']);
    }

    public function trigger(Request $request)
    {
        $tracer = Globals::tracerProvider()->getTracer('laravel');
        $span = $tracer->spanBuilder('manual-span')->startSpan();
        $scope = $span->activate();

        Log::info('Triggered manual span', [
            'endpoint' => '/trigger',
            'organization.name' => 'XPTO Corp'
        ]);

        $span->end();
        $scope->detach();

        return response()->json(['message' => 'Manual trace span sent to the collector']);
    }

    public function triggerContext(Request $request)
    {
        $tracer = Globals::tracerProvider()->getTracer('laravel');
        $span = $tracer->spanBuilder('manual-context-span')->startSpan();
        $scope = $span->activate();

        $span->setAttribute('user.id', '12345');
        $span->setAttribute('session.id', 'abcde');

        Log::info('Manual span with custom context triggered', [
            'endpoint' => '/trigger-context',
            'organization.name' => 'XPTO Corp'
        ]);

        $span->end();
        $scope->detach();

        return response()->json(['message' => 'Manual trace span with custom context attributes sent']);
    }

    public function health(Request $request)
    {
        Log::info('Health check accessed', [
            'endpoint' => '/health',
            'organization.name' => 'XPTO Corp'
        ]);

        return response()->json(['status' => 'healthy']);
    }
}
