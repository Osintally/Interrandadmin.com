<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/*
|--------------------------------------------------------------------------
| Health Check Routes
|--------------------------------------------------------------------------
|
| These routes are used for Docker health checks and monitoring
|
*/

Route::get('/health', function () {
    $status = [
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'services' => []
    ];

    // Check database connection
    try {
        DB::connection()->getPdo();
        $status['services']['database'] = 'healthy';
    } catch (Exception $e) {
        $status['services']['database'] = 'unhealthy';
        $status['status'] = 'degraded';
    }

    // Check Redis connection
    try {
        Redis::ping();
        $status['services']['redis'] = 'healthy';
    } catch (Exception $e) {
        $status['services']['redis'] = 'unhealthy';
        $status['status'] = 'degraded';
    }

    // Check storage permissions
    try {
        $storagePath = storage_path();
        if (is_writable($storagePath)) {
            $status['services']['storage'] = 'healthy';
        } else {
            $status['services']['storage'] = 'unhealthy';
            $status['status'] = 'degraded';
        }
    } catch (Exception $e) {
        $status['services']['storage'] = 'unhealthy';
        $status['status'] = 'degraded';
    }

    $httpStatus = $status['status'] === 'ok' ? 200 : 503;
    
    return response()->json($status, $httpStatus);
});

Route::get('/health/ready', function () {
    // Readiness check - more comprehensive than health check
    $checks = [
        'database' => false,
        'redis' => false,
        'storage' => false,
        'config' => false
    ];

    // Database check
    try {
        DB::connection()->getPdo();
        $checks['database'] = true;
    } catch (Exception $e) {
        // Database not ready
    }

    // Redis check
    try {
        Redis::ping();
        $checks['redis'] = true;
    } catch (Exception $e) {
        // Redis not ready
    }

    // Storage check
    try {
        $storagePath = storage_path();
        if (is_writable($storagePath)) {
            $checks['storage'] = true;
        }
    } catch (Exception $e) {
        // Storage not ready
    }

    // Config check
    try {
        config('app.name');
        $checks['config'] = true;
    } catch (Exception $e) {
        // Config not ready
    }

    $allReady = array_reduce($checks, function ($carry, $check) {
        return $carry && $check;
    }, true);

    if ($allReady) {
        return response()->json(['status' => 'ready', 'checks' => $checks], 200);
    } else {
        return response()->json(['status' => 'not ready', 'checks' => $checks], 503);
    }
});



