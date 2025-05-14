<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\ObservabilityController;

Route::get('/', [ObservabilityController::class, 'root']);
Route::get('/trigger', [ObservabilityController::class, 'trigger']);
Route::get('/trigger-context', [ObservabilityController::class, 'triggerContext']);
Route::get('/health', [ObservabilityController::class, 'health']);
Route::get('/observability', function () {
    return view('observability'); // This loads the HTML page
});