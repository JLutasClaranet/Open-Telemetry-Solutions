<?php

namespace App\Providers;

//basic Ones
use Illuminate\Support\ServiceProvider;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\API\Globals;
use OpenTelemetry\SDK\Common\Time\ClockFactory;

//related to Resource
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SemConv\ResourceAttributes;
use OpenTelemetry\SDK\Common\Attribute\Attributes;

//Exporters

//Traces
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Sdk;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
//Logs
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\Contrib\Otlp\LogsExporter;
use OpenTelemetry\SDK\Logs\Processor\BatchLogRecordProcessor;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactory;

//METERS
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;



class ObservabilityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $clock = ClockFactory::getDefault();
        //resource
        $resource = ResourceInfoFactory::defaultResource()->merge(ResourceInfo::create(Attributes::create([
            ResourceAttributes::SERVICE_NAMESPACE => 'XPTO Corp',
            ResourceAttributes::SERVICE_NAME => 'laravel-app',
            ResourceAttributes::DEPLOYMENT_ENVIRONMENT_NAME => 'development',
        ])));

        //trace transporter
        $traceTransport = (new OtlpHttpTransportFactory())->create('http://otel-collector:4318/v1/traces', 'application/x-protobuf');
        // // Tracer setup

        $traceExporter = new SpanExporter($traceTransport);

        // TracerProvider with correct argument order
       // $spanProcessor = new BatchSpanProcessor($traceExporter, $clock);
        //$tracerProvider = new TracerProvider($spanProcessor, null, $resource);
        
        $tracerProvider = TracerProvider::builder()
        ->addSpanProcessor(
            new BatchSpanProcessor($traceExporter, $clock)
        )
        ->setResource($resource)
        ->setSampler(new ParentBased(new AlwaysOnSampler()))
        ->build();


      
        /* Globals::tracerProvider($tracerProvider); */

        //LOGS
         // Logger setup
         $logsTransport = (new OtlpHttpTransportFactory())->create('http://otel-collector:4318/v1/logs', 'application/x-protobuf');
         $logExporter = new LogsExporter($logsTransport);
 
         // Processor requires clock
         /* $logProcessor = new BatchLogRecordProcessor($logExporter, $clock);
  */
         // Create an AttributesFactory instance
       /*   $attributesFactory = new AttributesFactory();
         $instrumentationScopeFactory = new InstrumentationScopeFactory($attributesFactory); */
         // Logger provider with resource
        /*  $loggerProvider = new LoggerProvider($logProcessor,$instrumentationScopeFactory, $resource);
         Globals::loggerProvider($loggerProvider); */

         $loggerProvider = LoggerProvider::builder()
            ->setResource($resource)
            ->addLogRecordProcessor(
                new BatchLogRecordProcessor($logExporter, $clock)
            )
            ->build();

        //METRICS
        $reader = new ExportingReader(
            new MetricExporter(
                (new StreamTransportFactory())->create('php://stdout', 'application/json')
            )
        );
        

        $meterProvider = MeterProvider::builder()
            ->setResource($resource)
            ->addReader($reader)
            ->build();


        //FULL BUILD
        Sdk::builder()
            ->setTracerProvider($tracerProvider)
            ->setMeterProvider($meterProvider)
            ->setLoggerProvider($loggerProvider)
            ->setPropagator(TraceContextPropagator::getInstance())
            ->setAutoShutdown(true)
            ->buildAndRegisterGlobal();
        
    }

    public function boot(): void
    {
        // Nothing here for now
    }
}
