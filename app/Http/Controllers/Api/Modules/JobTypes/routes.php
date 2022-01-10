<?php

use App\Http\Controllers\Api\Modules\JobTypes\JobTypeController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('Job-types/create', [JobTypeController::class, 'createJobType']);
    Route::post('Job-types/update', [JobTypeController::class, 'updateJobType']);
    Route::post('Job-types/delete', [JobTypeController::class, 'softDeleteJobType']);
    Route::post('Job-types/restore', [JobTypeController::class, 'restoreJobType']);
});
Route::get('Job-types', [JobTypeController::class, 'getAllJobTypes']);
Route::get('Job-types/show', [JobTypeController::class, 'getJobTypeById']);
