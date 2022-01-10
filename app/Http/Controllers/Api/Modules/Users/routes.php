<?php

use App\Http\Controllers\Api\Modules\Users\{
    AdminController,
    CompanyDetailController,
    UserController,
    CompanyController,
    EducationController,
    ExperienceController,
    SkillController,
    UserDetailController,
};
use Illuminate\Support\Facades\Route;

//Public Routes
/********************* Authentication Routes *********************/
Route::post('/login',  [UserController::class, 'login']);
Route::post('/register',  [UserController::class, 'register']);

//Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',  [UserController::class, 'logout']);
    Route::post('/update-password',  [UserController::class, 'updatePassword']);

    /************************** Admin Routes **************************/
    Route::get('admins', [AdminController::class, 'getAllAdmins']);
    Route::get('admins/show', [AdminController::class, 'getAdminById']);
    Route::post('admins/create', [AdminController::class, 'createAdmin']);
    Route::post('admins/edit', [AdminController::class, 'updateAdmin']);
    Route::post('admins/delete', [AdminController::class, 'softDeleteAdmin']);
    Route::post('admins/restore', [AdminController::class, 'restoreDeleteAdmin']);

    /************************** User Routes **************************/
    Route::get('users', [UserController::class, 'getAllUsers']);
    Route::get('users/show',  [UserController::class, 'showUserById']);
    Route::post('users/edit',  [UserController::class, 'updateUser']);
    Route::post('users/delete',  [UserController::class, 'softDeleteUser']);
    Route::post('users/restore',  [UserController::class, 'restoreDeleteUser']);

    /************************* User Details Routes *************************/
    Route::get('users/details',  [UserDetailController::class, 'getUserDetails']);
    Route::post('users/details/create-or-update',  [UserDetailController::class, 'updateOrCreateUserDetails']);

    /************************* Experiences Routes *************************/
    Route::get('users/experiences',  [ExperienceController::class, 'getUserExperiences']);
    Route::post('users/experiences/create',  [ExperienceController::class, 'createUserExperience']);
    Route::post('users/experiences/update',  [ExperienceController::class, 'updateUserExperience']);
    Route::post('users/experiences/delete',  [ExperienceController::class, 'deleteUserExperience']);

    /************************* Educations Routes *************************/
    Route::get('users/educations',  [EducationController::class, 'getUserEducations']);
    Route::post('users/educations/create',  [EducationController::class, 'createUserEducation']);
    Route::post('users/educations/update',  [EducationController::class, 'updateUserEducation']);
    Route::post('users/educations/delete',  [EducationController::class, 'deleteUserEducation']);

    /************************* Company Routes *************************/
    Route::post('companies/create',  [CompanyController::class, 'createCompany']);
    Route::post('companies/edit',  [CompanyController::class, 'updateCompany']);
    Route::post('companies/delete',  [CompanyController::class, 'softDeleteCompany']);
    Route::post('companies/restore',  [CompanyController::class, 'restoreDeleteCompany']);

    /************************* Company Details Routes *************************/
    Route::post('companies/details/create-or-update',  [CompanyDetailController::class, 'updateOrCreateCompanyDetails']);

    /************************* Skills Routes *************************/
    Route::get('skills', [SkillController::class, 'getAllSkills']);
    Route::post('skills/create',  [SkillController::class, 'createSkill']);
    Route::post('skills/update',  [SkillController::class, 'updateSkill']);
    Route::post('skills/delete',  [SkillController::class, 'deleteSkill']);
});

/************************* Company Routes *************************/
Route::get('companies', [CompanyController::class, 'getAllCompanies']);
Route::get('companies/show', [CompanyController::class, 'getCompanyById']);

/************************* Company Details Routes *************************/
Route::get('companies/details',  [CompanyDetailController::class, 'getCompanyDetails']);
