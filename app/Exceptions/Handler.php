<?php

namespace App\Exceptions;

use App\Http\Traits\ApiResponseTrait;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;
use TypeError;

class Handler extends ExceptionHandler
{
    use ApiResponseTrait;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation'
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {

        if ($e instanceof NotFoundHttpException) {
            return $this->apiResponse(404, "error 404", $request->url() . ' Not Found, try with correct url');
        }
        if ($e instanceof MethodNotAllowedHttpException) {
            return $this->apiResponse(405, "error 405", $request->method() . ' method Not allow for this route, try with correct method');
        }
        if ($e instanceof AuthenticationException) {
            return $this->apiResponse(404, "Authorization Token not found", $e->getMessage());
        }
        if ($e instanceof QueryException) {
            return $this->apiResponse(400, "error", $e->getMessage());
        }
        if ($e instanceof ValidationException) {
            return $this->apiResponse(400, "Validation errors", $e->errors());
        }
        if ($e instanceof TypeError) {
            return $this->apiResponse(400, "error", $e->getMessage());
        }
    }
}


