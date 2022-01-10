<?php

use App\Http\Controllers\Api\Modules\Jobs\JobController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('jobs', [JobController::class, 'getAllJobs']);
    Route::get('jobs/applied', [JobController::class, 'getUserJobs']);
    Route::post('jobs/apply', [JobController::class, 'userApplyJob']);
    Route::post('jobs/create', [JobController::class, 'createJob']);
    Route::post('jobs/update', [JobController::class, 'updateJob']);
    Route::post('jobs/delete', [JobController::class, 'softDeleteJob']);
    Route::post('jobs/restore', [JobController::class, 'restoreJob']);
    Route::post('jobs/users/approve', [JobController::class, 'approveUserJob']);
    Route::post('jobs/companies/approve', [JobController::class, 'approveCompanyJob']);
});
Route::get('jobs/show', [JobController::class, 'getJobById']);
Route::get('jobs/search',  [JobController::class, 'filterJobs']);
