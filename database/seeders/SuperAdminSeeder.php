<?php

namespace Database\Seeders;

use App\Http\Controllers\Api\Modules\Permissions\Permission;
use App\Http\Controllers\Api\Modules\Roles\Role;
use App\Http\Controllers\Api\Modules\Users\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = Permission::pluck('id')->all();
//        $role = Role::create(['name' => "super-admin"]);
//        $role->permissions()->sync($permissions);

        $roles = ['super-admin', 'Company', 'User', 'Admin'];
        foreach($roles as $role){
            Role::create([
                'name' => $role,
            ]);
        }

        $roleSuperAdmin = Role::where('name', 'super-admin')->first();
        $roleAdmin = Role::where('name', 'Admin')->first();
        $roleUser = Role::where('name', 'User')->first();
        $roleCompany = Role::where('name', 'Company')->first();


        User::create([
            'name'     => 'super admin',
            'email'    => 'super-admin@gmail.com',
            'password' => Hash::make("123456"),
            'role_id' => $roleSuperAdmin->id
        ]);

        User::create([
            'name'     => 'admin',
            'email'    => 'admin@gmail.com',
            'password' => Hash::make("123456"),
            'role_id' => $roleAdmin->id
        ]);

        User::create([
            'name'     => 'user',
            'email'    => 'user@gmail.com',
            'password' => Hash::make("123456"),
            'role_id' => $roleUser->id
        ]);

        User::create([
            'name'     => 'company',
            'email'    => 'company@gmail.com',
            'password' => Hash::make("123456"),
            'role_id' => $roleCompany->id
        ]);






    }
}
