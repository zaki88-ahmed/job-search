<?php

namespace App\Rules;

use App\Http\Controllers\Api\Modules\Permissions\Permission;
use Illuminate\Contracts\Validation\Rule;

class ValidPermissionId implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $values)
    {
        $permissionList = Permission::pluck('id')->toArray();
        foreach ($values as $value) {
            return in_array($value, $permissionList);
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid Permission Id.';
    }
}
