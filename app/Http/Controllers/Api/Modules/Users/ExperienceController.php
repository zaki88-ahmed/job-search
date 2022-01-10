<?php

namespace App\Http\Controllers\Api\Modules\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ApiResponseTrait;

class ExperienceController extends Controller
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware(['permissions:users-read'])->only(['getUserExperiences']);
        $this->middleware(['permissions:users-create'])->only('createUserExperience');
        $this->middleware(['permissions:users-update'])->only('updateUserExperience');
        $this->middleware(['permissions:users-delete'])->only('deleteUserExperience');
    }
    /**
     * @OA\Get(
     *      path="/api/users/experiences",
     *      operationId="get user experiences",
     *      tags={"User Experiences"},
     *      summary="Get user experiences",
     *      description="Returns user experiences",
     *      security={ {"sanctum": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="User experiences",
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
    public function getUserExperiences() {
        $user = auth('sanctum')->user();
        $experiences = Experince::where('user_id' , $user->id)->get();

        return $this->apiResponse(200, 'User experiences', null, $experiences);
    }

        /**
     * @OA\Post(
     *      path="/api/users/experiences/create",
     *      operationId="create new experience",
     *      tags={"User Experiences"},
     *      summary="Create new experience",
     *      description="Add new experience",
     *      security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass experience credentials",
     *          @OA\JsonContent(
     *              required={"start_date", "end_date", "title", "description", "company_name"},
     *              @OA\Property(property="start_date", type="string", format="start_date", example="1-1-2001"),
     *              @OA\Property(property="end_date", type="string", format="end_date", example="1-1-2001"),
     *              @OA\Property(property="title", type="string", format="title", example="test"),
     *              @OA\Property(property="description", type="string", format="description", example="this is description"),
     *              @OA\Property(property="company_name", type="string", format="company_name", example="Vodafone"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Experience created successfully",
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
    public function createUserExperience(Request $request) {
        $validation = Validator::make($request->all(), [
            'start_date'    => 'required',
            'end_date'      => 'required',
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'company_name'  => 'required|string|max:255',
        ]);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $user = auth('sanctum')->user();
        Experince::create([
            'user_id'       => $user->id,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'title'         => $request->title,
            'description'   => $request->description,
            'company_name'  => $request->company_name
        ]);
        return $this->ApiResponse(200,'Experience created successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/users/experiences/update",
     *      operationId="update new experience",
     *      tags={"User Experiences"},
     *      summary="update new experience",
     *      description="Add new experience",
     *      security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass experience credentials",
     *          @OA\JsonContent(
     *              required={"start_date", "end_date", "title", "description", "company_name", "experience_id"},
     *              @OA\Property(property="start_date", type="string", format="start_date", example="1-1-2001"),
     *              @OA\Property(property="end_date", type="string", format="end_date", example="1-1-2001"),
     *              @OA\Property(property="experience_id", type="string", format="experience_id", example="1"),
     *              @OA\Property(property="title", type="string", format="title", example="test"),
     *              @OA\Property(property="description", type="string", format="description", example="this is description"),
     *              @OA\Property(property="company_name", type="string", format="company_name", example="Vodafone"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Experience updated successfully",
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
    public function updateUserExperience(Request $request) {
        $validation = Validator::make($request->all(), [
            'experience_id'  => 'required|exists:experiences,id',
            'start_date'    => 'required',
            'end_date'      => 'required',
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'company_name'  => 'required|string|max:255',
        ]);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $user = auth('sanctum')->user();
        Experince::where('id' , $request->experience_id)->update([
            'user_id'       => $user->id,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'title'         => $request->title,
            'description'   => $request->description,
            'company_name'  => $request->company_name
        ]);
        return $this->ApiResponse(200, 'Experience updated successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/users/experiences/delete",
     *      operationId="delete specific experience",
     *      tags={"User Experiences"},
     *      summary="Soft delete experience",
     *      description="Soft delete experience",
     *      security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass experince credentials",
     *          @OA\JsonContent(
     *              required={"experince_id"},
     *              @OA\Property(property="experience_id", type="integer", format="experience_id", example="1"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="experience deleted successfully",
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
    public function deleteUserExperience(Request $request) {
        $validation = Validator::make($request->all(), ['experience_id' => 'required|exists:experiences,id']);
        if($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $experience = Experince::where('id' , $request->experience_id)->first();
        if (is_null($experience)) {
            return $this->ApiResponse(400, 'Experience already deleted');
        }
        $experience->delete();
        return $this->apiResponse(200,'Experience deleted successfully');
    }
}
