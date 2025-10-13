<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Template API Routes - Backward Compatibility
Route::middleware(['auth'])->group(function () {
    Route::get('/api/templates/{template}', [App\Http\Controllers\Performance\TemplateController::class, 'show'])
        ->name('api.templates.show');
    Route::get('/api/templates/{template}/weights', [App\Http\Controllers\Performance\TemplateController::class, 'getWeights'])
        ->name('api.templates.weights');
    Route::get('/api/templates/{template}/status', [App\Http\Controllers\Performance\TemplateController::class, 'getStatus'])
        ->name('api.templates.status');
    Route::post('/api/templates/bulk/publish', [App\Http\Controllers\Performance\TemplateController::class, 'bulkPublish'])
        ->name('api.templates.bulk.publish');
    Route::post('/api/templates/bulk/archive', [App\Http\Controllers\Performance\TemplateController::class, 'bulkArchive'])
        ->name('api.templates.bulk.archive');
});