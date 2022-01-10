<?php

namespace App\Http\Controllers\Api\Modules\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Controllers\Api\Modules\Roles\Role;
use Illuminate\Support\Facades\{
    Hash, Validator
};

class AdminController extends BaseController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware(['permissions:admins-read'])->only(['getAllAdmins', 'getAdminById']);
        $this->middleware(['permissions:admins-create'])->only('createAdmin');
        $this->middleware(['permissions:admins-update'])->only('updateAdmin');
        $this->middleware(['permissions:admins-delete'])->only(['softDeleteAdmin', 'restoreDeleteAdmin']);
    }
    /**
     * @OA\Get(
     *      path="/api/admins",
     *      operationId="get All Admins",
     *      tags={"Admins"},
     *      summary="Get list of Admins",
     *      description="Returns list of Admins",
     *      security={ {"sanctum": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="All Admins",
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
    public function getAllAdmins()
    {
        $adminRole = Role::where('name', 'Admin')->first();
        if (is_null($adminRole))
            return $this->ApiResponse(404, "No Role of admin");
        $admins = User::where('role_id', $adminRole->id)->get();
        return $this->ApiResponse(200, 'All Admins', NULL, $admins);
    }

    /**
     * @OA\Get(
     *      path="/api/admins/show",
     *      operationId="show specific admin",
     *      tags={"Admins"},
     *      summary="show specific admin",
     *      description="show specific admin",
     *      security={ {"sanctum": {} }},
     *      @OA\Parameter(
     *          name="admin_id",
     *          in="query",
     *          required=true,
     *          description="Enter admin id",
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Admin details",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Validation Error"
     *      )
     *  )
     */
    public function getAdminById(Request $request)
    {
        $validation = Validator::make($request->all(), ['admin_id' => 'required|exists:users,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $adminRole = Role::where('name', 'Admin')->first();
        if (is_null($adminRole)) {
            return $this->ApiResponse(404, "No Role of admin");
        }
        $admin = User::where([['role_id', $adminRole->id], ['id', $request->admin_id]])->first();
        if (is_null($admin)) {
            return $this->ApiResponse(400, 'This not an admin');
        }
        $admin = User::find($request->admin_id);
        return $this->ApiResponse(200, 'Admin details', null, $admin);
    }

    /**
     * @OA\Post(
     *      path="/api/admins/create",
     *      operationId="create Admin",
     *      tags={"Admins"},
     *      summary="Create Admin",
     *      description="Add new Admin",
     *      security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass admin data",
     *          @OA\JsonContent(
     *              required={"name", "email","password"},
     *              @OA\Property(property="name", type="string", example="Ahmed"),
     *              @OA\Property(property="email", type="email", example="test@gmail.com"),
     *              @OA\Property(property="password", type="password", example="123@test"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Admin created successfully",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Validation Error"
     *      )
     *     )
     */
    public function createAdmin(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6'
        ]);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }

        $adminRole = Role::where('name', 'Admin')->first();
        if (is_null($adminRole)) {
            return $this->ApiResponse(404, "No Role of admin");
        }

        $data = $request->all();
        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $adminRole->id
        ]);
        return $this->ApiResponse(200, 'Admin created successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/admins/edit",
     *      operationId="Edit specific admin",
     *      tags={"Admins"},
     *      summary="Edit specific admin",
     *      description="Edit admin",
     *      security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Enter admin data",
     *          @OA\JsonContent(
     *              required={"admin_id" , "name", "email", "password"},
     *              @OA\Property(property="admin_id", type="integer", format="admin_id", example="1"),
     *              @OA\Property(property="name", type="string", format="name", example="Ahmed"),
     *              @OA\Property(property="email", type="email", format="email", example="test@gmail.com"),
     *              @OA\Property(property="old_password", type="string", format="old_password", example="12345678"),
     *              @OA\Property(property="new_password", type="string", format="new_password", example="12345678")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Admin updated successfully",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Validation Errors"
     *      )
     *  )
     */
    public function updateAdmin(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'admin_id' => 'required|exists:users,id',
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$request->admin_id
        ]);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Errors', $validation->errors());
        }

        User::find($request->admin_id)->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);
        return $this->ApiResponse(200, 'Admin updated successfully');
    }

    /**
     * @OA\post(
     *      path="/api/admins/delete",
     *      operationId="Delete Admin",
     *      tags={"Admins"},
     *      summary="Delete Admin",
     *      description="Delete Admin",
     *      security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass admin data",
     *          @OA\JsonContent(
     *              required={"admin_id"},
     *              @OA\Property(property="admin_id", type="integer", example="1"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Admin detaiels",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Validation Error"
     *      )
     *  )
     */
    public function softDeleteAdmin(Request $request)
    {
        $validation = Validator::make($request->all(), ['admin_id' => 'required|exists:users,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $admin = User::find($request->admin_id);
        if (is_null($admin)) {
            return $this->ApiResponse(400, 'Admin already deleted');
        }
        $admin->delete();
        return $this->ApiResponse(200, 'Admin deleted successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/admins/restore",
     *      operationId="restore specific admin",
     *      tags={"Admins"},
     *      summary="Restore Delete Admin",
     *      description="restore admin",
     *      security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass admin credentials",
     *          @OA\JsonContent(
     *              required={"admin_id"},
     *              @OA\Property(property="admin_id", type="integer", format="admin_id", example="1"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Admin restored successfully",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Validation Error"
     *      )
     *  )
     */
    public function restoreDeleteAdmin(Request $request)
    {
        $validation = Validator::make($request->all(), ['admin_id' => 'required|exists:users,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $admin = User::withTrashed()->find($request->admin_id);
        if (!is_null($admin->deleted_at)) {
            $admin->restore();
            return $this->ApiResponse(200,'Admin restored successfully');
        }
        return $this->ApiResponse(200,'Admin already restored');
    }
}
