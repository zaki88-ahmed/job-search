<?php

namespace App\Http\Middleware;

use App\Http\Traits\ApiResponseTrait;
use Closure;
use Illuminate\Http\Request;

class Permissions
{
    use ApiResponseTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        foreach ($permissions as $permission) {
            $hasPermission = $request->user()->role->hasPermission($permission);
            if ($hasPermission) {
                return $next($request);
            }
            return $this->apiResponse(422, 'Don\'t have permission');
        }
    }
}
