<?php

namespace Database\Seeders;

use App\Http\Controllers\Api\Modules\Permissions\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'admins-read', 'admins-create', 'admins-update', 'admins-delete',
            'users-read', 'users-create', 'users-update', 'users-delete',
            'companies-read', 'companies-create', 'companies-update', 'companies-delete',
            'jobs-read', 'jobs-create', 'jobs-update', 'jobs-delete', 'admin-jobs-read',
            'categories-read', 'categories-create', 'categories-update', 'categories-delete',
            'locations-read', 'locations-create', 'locations-update', 'locations-delete',
            'jobTypes-read', 'jobTypes-create', 'jobTypes-update', 'jobTypes-delete',
            'permissions-read', 'permissions-create', 'permissions-update', 'permissions-delete',
            'roles-read', 'roles-create', 'roles-update', 'roles-delete',
            'approve-user', 'approve-company'
        ];
        foreach ($permissions as $permission) {
            Permission::create(['title' => $permission]);
        }



    }
}
