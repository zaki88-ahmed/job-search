<?php

namespace App\Http\Controllers\Api\Modules\Permissions;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class PermissionController extends Controller
{

    use ApiResponseTrait ;

    public function __construct()
    {
        $this->middleware(['permissions:permissions-read'])->only(['getAllPermissions', 'getPermissionById']);
        $this->middleware(['permissions:permissions-create'])->only('createPermission');
        $this->middleware(['permissions:permissions-update'])->only('updatePermission');
        $this->middleware(['permissions:permissions-delete'])->only(['softDeletePermission', 'restorePermission']);
    }
    /**
     * @OA\Get(
     *      path="/api/permissions",
     *      operationId="get permissions list",
     *      tags={"Permissions"},
     *      summary="Get list of permissions",
     *      description="Returns list of permissions",
     *     security={ {"sanctum": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="All Permissions",
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
    public function getAllPermissions() {
       $permissions = Permission::orderBy('id' , 'DESC')->get();
       return $this->apiResponse(200, 'All Permissions', null, $permissions);
    }

    /**
     * @OA\Get(
     *      path="/api/permissions/show",
     *      operationId="show specific permission",
     *      tags={"Permissions"},
     *      summary="show specific permission",
     *      description="show specific permission",
     *     security={ {"sanctum": {} }},
     *   @OA\Parameter(
     *    name="permission_id",
     *    in="query",
     *    required=true,
     *    description="Enter permission id",
     * ),
     *      @OA\Response(
     *          response=200,
     *          description="Permission details",
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
    public function getPermissionById(Request $request) {
        $validator = Validator::make($request->all(), ['permission_id' => 'required|exists:permissions,id']);

        if($validator->fails()){
            return $this->ApiResponse(400, 'Validation Error', $validator->errors());
        }

        $permission = Permission::find($request->permission_id);

        return $this->apiResponse(200, 'Permission details', null, $permission);

    }

    /**
     * @OA\Post(
     *      path="/api/permissions/add",
     *      summary="Add new permission",
     *      tags={"Permissions"},
     *      description="create new permission",
     *      security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Enter permission title",
     *          @OA\JsonContent(
     *              required={"title"},
     *          @OA\Property(property="title", type="string"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *      @OA\JsonContent(
     *          @OA\Property(property="user", type="object", ref="#/components/schemas/User"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Wrong credentials response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
     *          )
     *      )
     *  )
     */
    public function createPermission(Request $request) {
        $validator = Validator::make($request->all(), ['title' => 'required|unique:permissions|string|max:255']);

        if ($validator->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validator->errors());
        }

        Permission::create(['title' => $request->title]);

        return $this->apiResponse(200, 'permission added successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/permissions/edit",
     *      operationId="Edit specific permission",
     *      tags={"Permissions"},
     *      summary="Edit specific permission",
     *      description="Edit permission",
     *     security={ {"sanctum": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="Permission Edited successfully",
     *       ),
     *    @OA\RequestBody(
     *    required=true,
     *    description="Enter Permissions Id & title",
     *    @OA\JsonContent(
     *       required={"permission_id" , "title"},
     *       @OA\Property(property="permission_id", type="integer", format="permission_id", example="1"),
     *       @OA\Property(property="title", type="string", format="title", example="create"),
     *    ),
     * ),
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
    public function updatePermission(Request $request) {
        $validator = Validator::make($request->all(), [
            'permission_id' => 'required|exists:permissions,id',
            'title' => 'required|string|max:255|unique:permissions,title,'.$request->permission_id
        ]);
        if ($validator->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validator->errors());
        }

        $permission = Permission::where('id', $request->permission_id)->first();
        if (is_null($permission)) {
            return $this->ApiResponse(400, 'Permission already deleted');
        }

        $permission->update(['title' => $request->title]);
        return $this->apiResponse(200,'Permission updated successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/permissions/delete",
     *      operationId="delete specific permission",
     *      tags={"Permissions"},
     *      summary="delete specific permission",
     *      description="delete Permission",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *    required=true,
     *    description="Enter Permissions Id",
     *    @OA\JsonContent(
     *       required={"permission_id"},
     *       @OA\Property(property="permission_id", type="integer", format="permission_id", example="1"),
     *    ),
     * ),
     *      @OA\Response(
     *          response=200,
     *          description="Permission deleted successfully",
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
    public function softDeletePermission(Request $request) {
        $validator = Validator::make($request->all(), ['permission_id' => 'required|exists:permissions,id']);
        if($validator->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validator->errors());
        }

        $permission = Permission::where('id' , $request->permission_id)->first();
        if (is_null($permission)) {
            return $this->ApiResponse(400, 'Permission already deleted');
        }

        $permission->delete();
        return $this->apiResponse(200,'Permission deleted successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/permissions/restore",
     *      operationId="restore specific permission",
     *      tags={"Permissions"},
     *      summary="Restore delete permission",
     *      description="Restore permission",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass permission id",
     *          @OA\JsonContent(
     *              required={"permission_id"},
     *              @OA\Property(property="permission_id", type="integer", format="permission_id", example="1"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Permission restored successfully",
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
    public function restorePermission(Request $request)
    {
        $validation = Validator::make($request->all(), ['permission_id' => 'required|exists:permissions,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $permission = Permission::withTrashed()->find($request->permission_id);
        if (!is_null($permission->deleted_at)) {
            $permission->restore();
            return $this->ApiResponse(200,'Permission restored successfully');
        }
        return $this->ApiResponse(200,'Permission already restored');
    }
}
