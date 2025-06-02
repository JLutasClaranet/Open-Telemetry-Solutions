<?php
return [
    'otel_collector_traces_url' => env('OTEL_COLLECTOR_TRACES_URL', 'http://localhost:4318/v1/traces'),
    'otel_collector_logs_url' => env('OTEL_COLLECTOR_LOGS_URL', 'http://localhost:4318/v1/logs'),
    'otel_collector_metrics_url' => env('OTEL_COLLECTOR_METRICS_URL', 'http://localhost:4318/v1/metrics'),

    'service_namespace' => env('SERVICE_NAMESPACE', 'default-namespace'),
    'service_name' => env('SERVICE_NAME', 'default-service'),
    'deployment_environment' => env('DEPLOYMENT_ENVIRONMENT', 'production'),
    'organization_name' => env('ORGANIZATION_NAME', 'default-org'),
];