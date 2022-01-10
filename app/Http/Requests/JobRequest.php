<?php

namespace App\Http\Requests;

use App\Http\Traits\ApiResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;

class JobRequest extends FormRequest
{
    use ApiResponseTrait;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'job_id'                => 'nullable|exists:jobs,id',
            'title'                 => 'required',
            'salary_range'          => 'required',
            'requirements'          => 'required',
            'description'           => 'required',
            'years_of_experience'   => 'required|in:less than 1 year,1-3 years,3-5 years,5-7 years,more than 7 years',
            'category_id'           => 'required|exists:categories,id',
            'location_id'           => 'required|exists:locations,id',
            'job_type_id'           => 'required|exists:job_types,id'
        ];
    }
}
