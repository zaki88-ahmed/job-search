<?php

use App\Http\Controllers\Api\Modules\Categories\CategoryController;

Route::middleware(['auth:sanctum', 'roles:super-admin'])->group(function () {
    Route::post('category/add', [CategoryController::class, 'createCategory']);
    Route::post('category/update', [CategoryController::class, 'updateCategory']);
    Route::post('category/delete', [CategoryController::class, 'softDeleteCategory']);
    Route::post('category/restore', [CategoryController::class, 'restoreCategory']);
});
Route::get('categories', [CategoryController::class, 'getAllCategories']);
Route::get('category/show', [CategoryController::class, 'getCategoryById']);
