<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/trigger', function () {
    Log::info('Triggered manual span');
    return ['message' => 'Manual trace span sent'];
});

Route::get('/trigger-context', function () {
    Log::info('Triggered context trace', ['user.id' => 12345, 'session.id' => 'abcde']);
    return ['message' => 'Manual trace with context sent'];
});

Route::get('/health', fn() => ['status' => 'healthy']);

Route::get('/observability', function () {
    return view('observability'); // This loads the HTML page
});