<?php


namespace App\Http\Controllers\Api\Modules\Users;

use App\Http\Controllers\Api\Modules\Roles\Role;
use App\Http\Traits\ApiResponseTrait;
use App\Rules\MatchOldPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Support\Facades\{
    Hash, Validator
};


class UserController extends BaseController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware(['permissions:users-read'])->only(['getAllUsers', 'showUserById']);
        $this->middleware(['permissions:users-update'])->only('updateUser');
        $this->middleware(['permissions:admins-delete'])->only(['softDeleteUser', 'restoreDeleteUser']);
    }
    /**
     * @OA\Post(
     * path="/api/register",
     * summary="register",
     * description="register by name , email and password",
     * operationId="authLogin",
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Fill your Data",
     *    @OA\JsonContent(
     *       required={"name", "email","password"},
     *       @OA\Property(property="name", type="string", example="User"),
     *       @OA\Property(property="email", type="string", format="email", example="user@gmail.com"),
     *       @OA\Property(property="password", type="string", format="password", example="123456"),
     *    ),
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="user", type="object", ref="#/components/schemas/User"),
     *     )
     *  ),
     * @OA\Response(
     *    response=422,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
     *        )
     *     )
     * )
     *
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                  => 'required',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6'
        ]);
        if($validator->fails()) {
            return $this->ApiResponse(400, 'Validation Errors', $validator->errors());
        }
        $roleUser = Role::where('name', 'User')->first();
        if (is_null($roleUser)) {
            return $this->ApiResponse(400, "No Role of user");
        }
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $roleUser->id
        ]);
        return $this->ApiResponse(200, 'You have signed-in');
    }

    /**
     * @OA\Post(
     * path="/api/login",
     * summary="Sign in",
     * description="Login by email and password",
     * operationId="authLogin",
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string", format="email", example="user@mail.com"),
     *       @OA\Property(property="password", type="string", format="password", example="123456"),
     *       @OA\Property(property="persistent", type="boolean", example="true"),
     *    ),
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="user", type="object", ref="#/components/schemas/User"),
     *     )
     *  ),
     * @OA\Response(
     *    response=422,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
     *        )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->ApiResponse(400, 'Validation Errors', $validator->errors());
        }

        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->ApiResponse(401, 'Bad credentials');
        }

        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $user= Auth::user();
            $response = [
              'token' => $user->createToken('token-name')->plainTextToken
            ];
            return $this->ApiResponse(200, 'Done', null, $response);
        }
    }

    /**
     * @OA\Post(
     * path="/api/logout",
     * summary="Logout",
     * description="Logout by email, password",
     * operationId="authLogout",
     * tags={"Authentication"},
     * security={ {"sanctum": {} }},
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="user", type="object", ref="#/components/schemas/User"),
     *     )
     *  ),
     * @OA\Response(
     *    response=422,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
     *        )
     *     )
     * )
     */
    public function logout()
    {
        $user = auth('sanctum')->user();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
        return $this->ApiResponse(200, 'Logged out');
    }

    /**
     * @OA\Post(
     *      path="/api/users/update-password",
     *      operationId="update password",
     *      tags={"Authentication"},
     *      summary="Update password",
     *      description="Update password",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass user credentials",
     *          @OA\JsonContent(
     *              required={"old_password", "new_password"},
     *              @OA\Property(property="old_password", type="string", format="old_password", example="12345678"),
     *              @OA\Property(property="new_password", type="string", format="new_password", example="123456789"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Password update successfully",
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
    public function updatePassword(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'old_password' => ['required', new MatchOldPassword],
            'new_password' => 'required|min:6'
        ]);
        if($validation->fails()) {
            return $this->apiResponse(400, 'validation error', $validation->errors());
        }
        User::find(auth('sanctum')->user()->id)->update([
            'password' => Hash::make($request->new_password),
        ]);
        return $this->apiResponse(200, 'Password updated successfully');
    }

    /**
     * @OA\Get(
     *      path="/api/users",
     *      operationId="get all users",
     *      tags={"Users"},
     *      summary="Get list of users",
     *      description="Returns list of users",
     *     security={ {"sanctum": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="All users",
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
    public function getAllUsers()
    {
        $userRole = Role::where('name', 'User')->first();
        if (is_null($userRole)) {
            return $this->ApiResponse(400, "No Role of user");
        }
        $users = User::where('role_id', $userRole->id)->with('userDetail')->get();
        return $this->ApiResponse(200, 'All Users', NULL, $users);
    }

    /**
     * @OA\Get(
     *      path="/api/users/show",
     *      operationId="show specific User",
     *      tags={"Users"},
     *      summary="show specific user",
     *      description="show specific user",
     *     security={ {"sanctum": {} }},
     *   @OA\Parameter(
     *    name="user_id",
     *    in="query",
     *    required=true,
     *    description="Enter User id",
     *    ),
     *      @OA\Response(
     *          response=200,
     *          description="User details",
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

    public function showUserById(Request $request)
    {
        $validation = Validator::make($request->all(), ['user_id' => 'required|exists:users,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $userRole = Role::where('name', 'User')->first();
        if (is_null($userRole)) {
            return $this->ApiResponse(400, "No Role of user");
        }
        $user = User::where([['role_id', $userRole->id], ['id', $request->user_id]])->first();
        if (is_null($user)) {
            return $this->ApiResponse(400, 'This not an user');
        }
        $user = User::find($request->user_id)->with('userDetail')->first();
        return $this->ApiResponse(200, 'User details', null, $user);
    }

    /**
     * @OA\Post(
     *      path="/api/users/edit",
     *      operationId="update user",
     *      tags={"Users"},
     *      summary="Update user",
     *      description="Edit user",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass user credentials",
     *          @OA\JsonContent(
     *              required={"name", "email", "password","user_id"},
     *              @OA\Property(property="user_id", type="integer", format="user_id", example="1"),
     *              @OA\Property(property="name", type="string", format="name", example="user"),
     *              @OA\Property(property="email", type="string", format="email", example="user@gmail.com"),
     *              @OA\Property(property="password", type="string", format="password", example="123456")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User update successfully",
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
    public function updateUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$request->user_id,
        ]);
        if ($validator->fails()) {
            return $this->ApiResponse(400, 'Validation Errors', $validator->errors());
        }
        $userRole = Role::where('name', 'User')->first();
        if (is_null($userRole)) {
            return $this->ApiResponse(400, "No Role of user");
        }
        $user = User::where([['role_id', $userRole->id], ['id', $request->user_id]])->first();
        if (is_null($user)) {
            return $this->ApiResponse(400, 'This not an user');
        }

        User::find($request->user_id)->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);
        return $this->apiResponse(200, 'User updated successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/users/delete",
     *      operationId="delete specific user",
     *      tags={"Users"},
     *      summary="Soft delete user",
     *      description="Soft delete user",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass user credentials",
     *          @OA\JsonContent(
     *              required={"user_id"},
     *              @OA\Property(property="user_id", type="integer", format="user_id", example="1"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User deleted successfully",
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
    public function softDeleteUser(Request $request)
    {
//        dd('cc');

        $validation = Validator::make($request->all(), ['user_id' => 'required|exists:users,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $user = User::find($request->user_id);
        if (is_null($user)) {
            return $this->ApiResponse(400, 'No user Found');
        }
        $user->delete();
        return $this->apiResponse(200,'User deleted successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/users/restore",
     *      operationId="restore specific user",
     *      tags={"Users"},
     *      summary="Restore delete user",
     *      description="restore user",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass user credentials",
     *          @OA\JsonContent(
     *              required={"user_id"},
     *              @OA\Property(property="user_id", type="integer", format="user_id", example="1"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User restored successfully",
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
    public function restoreDeleteUser(Request $request)
    {
//        dd('aa');
        $validation = Validator::make($request->all(), ['user_id' => 'required|exists:users,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $user = User::withTrashed()->find($request->user_id);
        if (!is_null($user->deleted_at)) {
            $user->restore();
            return $this->ApiResponse(200,'User restored successfully');
        }
        return $this->ApiResponse(200,'User already restored');
    }
}
?>
