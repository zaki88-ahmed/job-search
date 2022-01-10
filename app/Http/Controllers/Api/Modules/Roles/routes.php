<?php

use App\Http\Controllers\Api\Modules\Roles\RoleController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('roles', [RoleController::class, 'getAllRoles']);
    Route::get('roles/show', [RoleController::class, 'getRoleById']);
    Route::post('roles/add', [RoleController::class, 'createRole']);
    Route::post('roles/update', [RoleController::class, 'updateRole']);
    Route::post('roles/delete', [RoleController::class, 'softDeleteRole']);
    Route::post('roles/restore', [RoleController::class, 'restoreRole']);

});
