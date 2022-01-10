<?php

use App\Http\Controllers\Api\Modules\Permissions\PermissionController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('permissions', [PermissionController::class, 'getAllPermissions']);
    Route::get('permissions/show', [PermissionController::class, 'getPermissionById']);
    Route::post('permissions/add', [PermissionController::class, 'createPermission']);
    Route::post('permissions/edit', [PermissionController::class, 'updatePermission']);
    Route::post('permissions/delete', [PermissionController::class, 'softDeletePermission']);
    Route::post('permissions/restore', [PermissionController::class, 'restorePermission']);
});
