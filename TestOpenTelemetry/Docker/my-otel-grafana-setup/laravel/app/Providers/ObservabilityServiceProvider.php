<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\Exporter\OTLP\SpanExporter;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Time\Clock;
use OpenTelemetry\Exporter\OTLP\LogsExporter;
use OpenTelemetry\SDK\Logs\Processor\BatchLogRecordProcessor;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricReader\PeriodicMetricReader;
use OpenTelemetry\Exporter\OTLP\MetricExporter; // Corrected import for MetricExporter
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\Exporter\OTLP\OtlpHttpTransportFactory;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;

class ObservabilityServiceProvider extends ServiceProvider
{
    public function register()
    {
        // === Resource definition ===
        $resource = ResourceInfo::create(Attributes::create([  // Using the correct Attributes class here
            'service.name' => 'laravel-service',
            'organization.name' => 'XPTO Corp',
        ]));

        // === Tracing ===
        // Create the OTLP SpanExporter
        $traceExporter = new SpanExporter(
            $this->createTransport()
        );

        // Get the default ClockInterface object using Clock::getDefault() method
        $clock = Clock::getDefault();  // Correct method to get ClockInterface

        // Get options for the BatchSpanProcessor directly from the method
        $scheduledDelayMillis = 1000;
        $maxQueueSize = 2048;
        $maxExportBatchSize = 512;

        // Create the BatchSpanProcessor with the clock and additional options
        $batchProcessor = new BatchSpanProcessor(
            $traceExporter, 
            $clock, 
            $scheduledDelayMillis, 
            $maxQueueSize, 
            $maxExportBatchSize
        );

        // Create the TracerProvider
        $tracerProvider = new TracerProvider(
            $batchProcessor // This is the first parameter
        );

        // Register the TracerProviderInterface in the container
        $this->app->singleton(TracerProviderInterface::class, fn () => $tracerProvider);

        // === Logging ===
        // Set up transport for LogsExporter using the OtlpHttpTransportFactory
        $logTransport = $this->createTransport();

        // Create LogsExporter with the transport
        $logExporter = new LogsExporter($logTransport);

        // Directly pass the configuration options instead of using BatchLogProcessorOptions
        $logProcessor = new BatchLogRecordProcessor(
            $logExporter, 
            $clock, 
            $scheduledDelayMillis,  // Example option for delay
            $maxQueueSize = 2048,          // Example option for queue size
            $maxExportBatchSize = 512      // Example option for max batch size
        );

        // Create an instance of AttributesFactory (implements AttributesFactoryInterface)
        $instrumentationScopeFactory = new InstrumentationScopeFactory();

        // Create LoggerProvider and pass the logProcessor and instrumentationScopeFactory as arguments
        $loggerProvider = new LoggerProvider($logProcessor, $instrumentationScopeFactory);

        // Register logger in the container
        $this->app->singleton('otel.logger', fn () => $loggerProvider->getLogger('laravel-app'));

        // === Metrics ===
        // Use the OtlpHttpTransportFactory to create the transport
        $metricTransport = $this->createTransport();

        // Create MetricExporter using the transport
        $metricExporter = new MetricExporter($metricTransport);

        $reader = new PeriodicMetricReader($metricExporter);
        $meterProvider = new MeterProvider(
            metricReaders: [$reader],
            resourceInfo: $resource
        );
        $this->app->singleton(MeterProvider::class, fn () => $meterProvider);
    }

    public function boot()
    {
        // Optional post-registration actions can go here
    }

    /**
     * Create the transport for sending logs (likely an HTTP transport).
     * 
     * @return TransportInterface The transport interface
     */
    private function createTransport(): TransportInterface
    {
        // Use the OtlpHttpTransportFactory to create the transport
        return OtlpHttpTransportFactory::create(
            'http://otel-collector:4318/v1/logs',  // Endpoint for logs
            'application/x-protobuf',              // Content type (assuming Protobuf is used)
            [],                                    // Custom headers (empty in this case)
            null,                                  // Compression (default to none)
            10.0,                                  // Timeout in seconds
            100,                                   // Retry delay in milliseconds
            3,                                     // Max retries
            null,                                  // CA certificate
            null,                                  // SSL certificate
            null                                   // SSL key
        );
    }
}
