<?php

namespace App\Http\Controllers\Api\Modules\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Validator;

class EducationController extends Controller
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware(['permissions:users-read'])->only(['getUserEducations']);
        $this->middleware(['permissions:users-create'])->only('createUserEducation');
        $this->middleware(['permissions:users-update'])->only('updateUserEducation');
        $this->middleware(['permissions:users-delete'])->only('deleteUserEducation');
    }
    /**
     * @OA\Get(
     *      path="/api/users/educations",
     *      operationId="get user educations",
     *      tags={"User Educations"},
     *      summary="Get user educations",
     *      description="Returns user educations",
     *      security={ {"sanctum": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="user educations",
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
    public function getUserEducations() {
        $user = auth('sanctum')->user();
        $educations = Education::where('user_id' , $user->id)->get();

        return $this->apiResponse(200, 'User educations', null, $educations);
    }

            /**
     * @OA\Post(
     *      path="/api/users/educations/create",
     *      operationId="create new educations",
     *      tags={"User Educations"},
     *      summary="Create new educations",
     *      description="Add new educations",
     *      security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass educations credentials",
     *          @OA\JsonContent(
     *              required={"start_date", "end_date", "university"},
     *              @OA\Property(property="start_date", type="string", format="start_date", example="1-1-2001"),
     *              @OA\Property(property="end_date", type="string", format="end_date", example="1-1-2001"),
     *              @OA\Property(property="university", type="string", format="university", example="ASU"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Educations created successfully",
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
    public function createUserEducation(Request $request) {
        $validation = Validator::make($request->all(), [
            'start_date'    => 'required',
            'end_date'      => 'required',
            'university'    => 'required|string|max:255',
        ]);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $user = auth('sanctum')->user();
        Education::create([
            'user_id'       => $user->id,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'university'    => $request->university,
        ]);
        return $this->ApiResponse(200,'Education created successfully');
    }

                /**
     * @OA\Post(
     *      path="/api/users/educations/update",
     *      operationId="update new educations",
     *      tags={"User Educations"},
     *      summary="update new educations",
     *      description="Add new educations",
     *      security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass educations credentials",
     *          @OA\JsonContent(
     *              required={"start_date", "end_date", "university", "education_id"},
     *              @OA\Property(property="start_date", type="string", format="start_date", example="1-1-2001"),
     *              @OA\Property(property="end_date", type="string", format="end_date", example="1-1-2001"),
     *              @OA\Property(property="education_id", type="string", format="education_id", example="1"),
     *              @OA\Property(property="university", type="string", format="university", example="EUC"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="educations updated successfully",
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
    public function updateUserEducation(Request $request) {
        $validation = Validator::make($request->all(), [
            'education_id' => 'required|exists:educations,id',
            'start_date'    => 'required',
            'end_date'      => 'required',
            'university'    => 'required|string|max:255',
        ]);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $user = auth('sanctum')->user();
        Education::where('id' , $request->education_id)->update([
            'id'            => $request->education_id,
            'user_id'       => $user->id,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'university'    => $request->university
        ]);
        return $this->ApiResponse(200, 'Education updated successfully');
    }


    /**
     * @OA\Post(
     *      path="/api/users/educations/delete",
     *      operationId="delete specific education",
     *      tags={"User Educations"},
     *      summary="Soft delete education",
     *      description="Soft delete education",
     *      security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass education credentials",
     *          @OA\JsonContent(
     *              required={"education_id"},
     *              @OA\Property(property="education_id", type="integer", format="education_id", example="1"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Education deleted successfully",
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
    public function deleteUserEducation(Request $request) {
        $validation = Validator::make($request->all(), ['education_id' => 'required|exists:educations,id']);
        if($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $education = Education::where('id' , $request->education_id)->first();
        if (is_null($education)) {
            return $this->ApiResponse(400, 'education already deleted');
        }

        $education->delete();
        return $this->apiResponse(200,'Education deleted successfully');
    }
}
