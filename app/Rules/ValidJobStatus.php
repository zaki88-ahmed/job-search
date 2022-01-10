<?php

namespace App\Rules;

use App\Http\Controllers\Api\Modules\Jobs\Job;
use Illuminate\Contracts\Validation\Rule;

class ValidJobStatus implements Rule
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
    public function passes($attribute, $value)
    {
        return Job::where('id', $value)->first()->is_published == "accepted";
    }


    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'This job is not published yet';
    }
}
