<?php

use App\Http\Controllers\Api\Modules\Locations\LocationController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('locations/create', [LocationController::class, 'createLocation']);
    Route::post('locations/update', [LocationController::class, 'updateLocation']);
    Route::post('locations/delete', [LocationController::class, 'softDeleteLocation']);
    Route::post('locations/restore', [LocationController::class, 'restoreLocation']);
});
Route::get('locations', [LocationController::class, 'getAllLocations']);
Route::get('locations/show', [LocationController::class, 'getLocationById']);
