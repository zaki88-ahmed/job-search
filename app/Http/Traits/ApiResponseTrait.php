<?php
namespace App\Http\Traits;

trait ApiResponseTrait{

    /** Build:
     * [
     *  Code => Base [200]
     *  status =>  [200 - 201 - 2002 - 400 - 404 - 422]
     *  message => "Write message here"
     *  errors / data => if status is [200 - 201 - 202] that main success so return data,
     *  but if status [400 - 404 - 422] that main some errors so return errors.
     * ]
     */

    public function apiResponse($code = 200, $message = null, $errors = null, $data = null){

        $array = [
            'status' => $code,
            'message' => $message,

        ];
        if(is_null($data) && !is_null($errors)){
            $array['errors'] = $errors;
        }elseif(is_null($errors) && !is_null($data)){
            $array['data'] = $data;
        }

        return response($array , 200);
    }
}

?>
