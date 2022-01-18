<?php

namespace App\Http\Controllers\Api\Modules\Users;

use App\Http\Controllers\Api\Modules\Roles\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Hash, Validator
};

class CompanyController extends Controller
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware(['permissions:companies-create'])->only('createCompany');
        $this->middleware(['permissions:companies-update'])->only('updateCompany');
        $this->middleware(['permissions:companies-delete'])->only(['softDeleteCompany', 'restoreDeleteCompany']);
    }
    /**
     * @OA\Get(
     *      path="/api/companies",
     *      operationId="Get All Companies",
     *      tags={"Companies"},
     *      summary="Get list of companies",
     *      description="Returns list of companies",
     *      @OA\Response(
     *          response=200,
     *          description="All companies",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function getAllCompanies()
    {
        $roleCompany = Role::where('name', 'Company')->first();
        if (is_null($roleCompany)) {
            return $this->ApiResponse(404, "No role of company found");
        }
        $companies = User::where('role_id', $roleCompany->id)->get();
        return $this->ApiResponse(200, "ALl Companies", NULL, CompanyResource::collection($companies));
    }

    /**
     * @OA\Get(
     *      path="/api/companies/show",
     *      operationId="show specific company",
     *      tags={"Companies"},
     *      summary="show specific company",
     *      description="show specific company",
     *      @OA\Parameter(
     *          name="company_id",
     *          in="query",
     *          required=true,
     *          description="Enter company id",
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Company details",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *  )
     */
    public function getCompanyById(Request $request)
    {
//        dd($request->all());
        $roleCompany = Role::where('name', 'Company')->first();
//        dd($roleCompany);
        if (is_null($roleCompany)) {
            return $this->ApiResponse(404, "No role of company found");
        }
        $company = User::where([['id', request('company_id')], ['role_id', $roleCompany->id]])->first();
        if (is_null($company)) {
            return $this->ApiResponse(404, "This company not found");
        }
        return $this->ApiResponse(200, "Company Details", NULL, CompanyResource::make($company));
    }

    /**
     * @OA\Post(
     *      path="/api/companies/create",
     *      operationId="create new company",
     *      tags={"Companies"},
     *      summary="Create New Company",
     *      description="Add New Company",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass companies credentials",
     *          @OA\JsonContent(
     *              required={"name", "email", "password"},
     *              @OA\Property(property="name", type="string", format="name", example="comapny"),
     *              @OA\Property(property="email", type="string", format="email", example="comapny@gmail.com"),
     *              @OA\Property(property="password", type="string", format="password", example="123456"),
     *              @OA\Property(property="password_confirmation", type="string", format="password", example="123456")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Company created successfully",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *        @OA\Response(
     *          response=400,
     *          description="Validation Error"
     *      )
     *     )
     */
    public function createCompany(Request $request)
    {
        $validator = Validator::make($request->all() , [
            "name"                  => "required",
            "email"                 => "required|unique:users,email",
            'password'              => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6'
        ]);
        if($validator->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validator->errors());
        }

        $roleCompany = Role::where('name', 'Company')->first();
        if (is_null($roleCompany)) {
            return $this->ApiResponse(404, "No Role of Company");
        }
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $roleCompany->id
        ]);
        return $this->ApiResponse(200, "Company created successfully");
    }

    /**
     * @OA\Post(
     *      path="/api/companies/edit",
     *      operationId="update company",
     *      tags={"Companies"},
     *      summary="Update company",
     *      description="Edit company",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass company credentials",
     *          @OA\JsonContent(
     *              required={"name", "email", "password"},
     *              @OA\Property(property="company_id", type="integer", format="company_id", example="1"),
     *              @OA\Property(property="name", type="string", format="name", example="comapny"),
     *              @OA\Property(property="email", type="string", format="email", example="comapny@gmail.com"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Company update successfully",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *        @OA\Response(
     *          response=400,
     *          description="Validation Error"
     *      )
     *     )
     */
    public function updateCompany(Request $request)
    {
        $validator = Validator::make($request->all() , [
            "company_id" => "required|exists:users,id",
            "name" =>  "required",
            "email" => "required|unique:users,email,".$request->company_id
        ]);
        if($validator->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validator->errors());
        }
//        dd($request->all());
        User::find($request->company_id)->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);
        return $this->ApiResponse(200, "Company update successfully");
    }


    /**
     * @OA\Post(
     *      path="/api/companies/delete",
     *      operationId="delete specific company",
     *      tags={"Companies"},
     *      summary="Soft delete company",
     *      description="Soft delete company",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass role credentials",
     *          @OA\JsonContent(
     *              required={"company_id"},
     *              @OA\Property(property="company_id", type="integer", format="company_id", example="2"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Company deleted successfully",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *        @OA\Response(
     *          response=400,
     *          description="Validation Error"
     *      )
     *     )
     */
    public function softDeleteCompany(Request $request)
    {
        $validation = Validator::make($request->all() , ["company_id" => "required|exists:users,id"]);
        if($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $comapany = User::find($request->company_id);
        if (is_null($comapany)) {
            return $this->ApiResponse(400, 'Company already deleted');
        }
        $comapany->delete();
        return $this->ApiResponse(200, "Company deleted successfully");
    }

    /**
     * @OA\Post(
     *      path="/api/companies/restore",
     *      operationId="restore specific company",
     *      tags={"Companies"},
     *      summary="Restore delete comapny",
     *      description="restore comapny",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass comapny credentials",
     *          @OA\JsonContent(
     *              required={"company_id"},
     *              @OA\Property(property="company_id", type="integer", format="company_id", example="2"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Company restored successfully",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *        @OA\Response(
     *          response=400,
     *          description="Validation Error"
     *      )
     *     )
     */
    public function restoreDeleteCompany(Request $request)
    {
        $validation = Validator::make($request->all() , ["company_id" => "required|exists:users,id"]);

        if($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }

        $company = User::withTrashed()->find($request->company_id);
        if (!is_null($company->deleted_at)) {
            $company->restore();
            return $this->ApiResponse(200,'Company restored successfully');
        }
        return $this->ApiResponse(200,'Company already restored');
    }
}
