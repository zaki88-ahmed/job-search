<?php

namespace App\Http\Controllers\Api\Modules\Roles;

use App\Http\Controllers\Controller;
use App\Rules\ValidPermissionId;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware(['permissions:roles-read'])->only(['getAllRoles', 'getRoleById']);
        $this->middleware(['permissions:roles-create'])->only('createRole');
        $this->middleware(['permissions:roles-update'])->only('updateRole');
        $this->middleware(['permissions:roles-delete'])->only(['softDeleteRole', 'restoreRole']);
    }
    /**
     * @OA\Get(
     *      path="/api/roles",
     *      operationId="Get all roles",
     *      tags={"Roles"},
     *      summary="Get list of all roles",
     *      description="Returns list of all roles",
     *     security={ {"sanctum": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="All roles",
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
    public function getAllRoles()
    {
        $roles = Role::with("permissions:id,title")->get();
        return $this->apiResponse(200,'All Roles',null, $roles);
    }

    /**
     * @OA\Get(
     *      path="/api/roles/show",
     *      operationId="show specific role",
     *      tags={"Roles"},
     *      summary="show specific role",
     *      description="show specific role",
     *     security={ {"sanctum": {} }},
     *   @OA\Parameter(
     *    name="role_id",
     *    in="query",
     *    required=true,
     *    description="Enter role id",
     *    ),
     *      @OA\Response(
     *          response=200,
     *          description="Role details",
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

    public function getRoleById(Request $request)
    {
        $validation = Validator::make($request->all(), ['role_id' => 'required|exists:roles,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $role = Role::where('id', $request->role_id)->with("permissions:id,title")->first();
        return $this->ApiResponse(200, 'Role details', null, $role);
    }

    /**
     * @OA\Post(
     *      path="/api/roles/add",
     *      operationId="create new role",
     *      tags={"Roles"},
     *      summary="Create New role",
     *      description="Add New role",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass role credentials",
     *          @OA\JsonContent(
     *              required={"name", "permissions"},
     *              @OA\Property(property="name", type="string", format="name", example="role"),
     *              @OA\Property(property="permissions", type="integer", format="permissions", example="[1, 2]"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Role created successfully",
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
     *          description="Validations Error"
     *      )
     *     )
     */
    public function createRole(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required|unique:roles',
            'permissions' => ['required', 'array', new ValidPermissionId()],
            'permissions.*' => 'required'
        ]);

        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validations Error', $validation->errors());
        }

        $array = [];
        $permissions = $request->permissions;
//        for ($i = 0; $i < count($permissions); $i++) {
//            //Validation for Exist Group
//            if (in_array($permissions[$i], $array)) {
//                return $this->ApiResponse(422, 'Validation Error', 'This Permission is Exist');
//            }
//            $array[] = $permissions[$i];
//
//            //Validation for Valid Permission Id
//            $permissionsValidation = Validator::make($request->all(), ['permissions.'.$i => 'exists:permissions,id']);
//            if ($permissionsValidation->fails()) {
//                return $this->ApiResponse(422, 'Validation Error', $permissionsValidation->errors());
//            }
//        }
        $role = Role::create(['name' => $request->name]);
        $role->permissions()->sync($permissions);
        return $this->ApiResponse(200,'Role created successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/roles/update",
     *      operationId="update role",
     *      tags={"Roles"},
     *      summary="Update role",
     *      description="Edit role",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass role credentials",
     *          @OA\JsonContent(
     *              required={"name","permissions", "role_id"},
     *              @OA\Property(property="name", type="string", format="name", example="role"),
     *              @OA\Property(property="permissions", type="integer", format="permissions", example="[1, 2]"),
     *              @OA\Property(property="role_id", type="integer", format="role_id", example="2"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Role updated successfully",
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
     *          description="Validations Error"
     *      )
     *     )
     */
    public function updateRole(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'role_id'            => 'required|exists:roles,id',
            'name'               => 'required|unique:roles,name,'.$request->role_id,
            'permissions'        => ['required', 'array', new ValidPermissionId()],
            'permissions.*'      => 'required'
        ]);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validations Error', $validation->errors());
        }

        $role = Role::where('id' , $request->role_id)->first();
        if (is_null($role)) {
            return $this->ApiResponse(400, 'Role already deleted');
        }

        $array = [];
        $permissions = $request->permissions;
//        for ($i = 0; $i < count($permissions); $i++){
//
//            //Validation for Exist Group
//            if (in_array($permissions[$i], $array)) {
//                return $this->ApiResponse(422, 'Validation Error', 'This Permission is Exist');
//            }
//            $array[] = $permissions[$i];
//
//            //Validation for Valid Permission Id
//            $permissionsValidation = Validator::make($request->all(), ['permissions.'.$i => 'exists:permissions,id']);
//            if ($permissionsValidation->fails()) {
//                return $this->ApiResponse(422, 'Validation Error', $permissionsValidation->errors());
//            }
//        }
        $role = Role::find($request->role_id);
        $role->update(['name' => $request->name]);
        $role->permissions()->sync($permissions);
        return $this->ApiResponse(200,'Role updated successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/roles/delete",
     *      operationId="delete specific role",
     *      tags={"Roles"},
     *      summary="Soft Delete role",
     *      description="Soft delete role",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass role credentials",
     *          @OA\JsonContent(
     *              required={"role_id"},
     *              @OA\Property(property="role_id", type="integer", format="role_id", example="2"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Role deleted successfully",
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
    public function softDeleteRole(Request $request)
    {
        $validation = Validator::make($request->all(), ['role_id' => 'required|exists:roles,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $role = Role::find($request->role_id);
        if (is_null($role)) {
            return $this->ApiResponse(400, 'Role already deleted');
        }
        $role->delete();
        return $this->ApiResponse(200,'Role deleted successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/roles/restore",
     *      operationId="restore specific role",
     *      tags={"Roles"},
     *      summary="Restore delete role",
     *      description="restore role",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass role credentials",
     *          @OA\JsonContent(
     *              required={"role_id"},
     *              @OA\Property(property="role_id", type="integer", format="role_id", example="2"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Role restored successfully",
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
    public function restoreRole(Request $request)
    {
        $validation = Validator::make($request->all(), ['role_id' => 'required|exists:roles,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }

        $role = Role::withTrashed()->find($request->role_id);
        if (!is_null($role->deleted_at)) {
            $role->restore();
            return $this->ApiResponse(200,'Role restored successfully');
        }
        return $this->ApiResponse(200,'Role already restored');
    }
}
