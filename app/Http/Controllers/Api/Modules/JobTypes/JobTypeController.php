<?php

namespace App\Http\Controllers\Api\Modules\JobTypes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Validator;

class JobTypeController extends Controller
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware(['permissions:jobTypes-read'])->only(['getAllJobTypes', 'getJobTypeById']);
        $this->middleware(['permissions:jobTypes-create'])->only('createJobType');
        $this->middleware(['permissions:jobTypes-update'])->only('updateJobType');
        $this->middleware(['permissions:jobTypes-delete'])->only(['softDeleteJobType', 'restoreJobType']);
    }

     /**
     * @OA\Get(
     *      path="/api/Job-types",
     *      operationId="Get all JobTypes",
     *      tags={"JobTypes"},
     *      summary="Get list of all JobTypes",
     *      description="Returns list of all JobTypes",
     *      @OA\Response(
     *          response=200,
     *          description="All JobTypes",
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
    public function getAllJobTypes()
    {
        $JobTypes = JobType::with('jobs')->get();
        return $this->apiResponse(200,'All JobTypes',null, $JobTypes);
    }

    /**
     * @OA\Post(
     *      path="/api/Job-types/create",
     *      operationId="create new JobType",
     *      tags={"JobTypes"},
     *      summary="Create new JobType",
     *      description="Add new JobType",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Enter JobType data",
     *          @OA\JsonContent(
     *              required={"title"},
     *              @OA\Property(property="title", type="string", format="title", example="Software developer")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="JobType created successfully",
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
    public function createJobType (Request $request)
    {
        $validation = Validator::make($request->all(), ['title' => 'required']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
         $jobType = JobType::create(['title' => $request->title]);
        return $this->ApiResponse(200,'JobType created successfully', null, $jobType);
    }

    /**
     * @OA\Get(
     *      path="/api/Job-types/show",
     *      operationId="show specific JobType",
     *      tags={"JobTypes"},
     *      summary="show specific JobType",
     *      description="show specific JobType",
     *     security={ {"sanctum": {} }},
     *   @OA\Parameter(
     *    name="JobType_id",
     *    in="query",
     *    required=true,
     *    description="Enter JobType id",
     *    ),
     *      @OA\Response(
     *          response=200,
     *          description="JobType details",
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
    public function getJobTypeById(Request $request)
    {
        $validation = Validator::make($request->all(), ['JobType_id' => 'required|exists:JobTypes,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $JobType = JobType::where('id', $request->JobType_id)->with('jobs')->first();
        return $this->ApiResponse(200, 'JobType details', null, $JobType);
    }
    /**
     * @OA\Post(
     *      path="/api/Job-types/update",
     *      operationId="update JobType",
     *      tags={"JobTypes"},
     *      summary="Update JobType",
     *      description="Edit JobType",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Enter JobType data",
     *          @OA\JsonContent(
     *              required={"JobType_id","title"},
     *              @OA\Property(property="JobType_id", type="integer", format="JobType_id", example="1"),
     *              @OA\Property(property="title", type="string", format="title", example="Software developer")
     *      ),
     * ),
     *      @OA\Response(
     *          response=200,
     *          description="JobType updated successfully",
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
    public function updateJobType(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'JobType_id'  => 'required|exists:job_types,id',
            'title'       => 'required'
        ]);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $jobType = JobType::find($request->JobType_id);
//        $jobType = JobType::where(['id', $request->JobType_id]);
//        dd($jobType);
        $jobType->update(['title'  => $request->title]);
        return $this->ApiResponse(200,'JobType updated successfully', null, $jobType);
    }

    /**
     * @OA\Post(
     *      path="/api/Job-types/delete",
     *      operationId="delete specific JobType",
     *      tags={"JobTypes"},
     *      summary="Soft delete JobType",
     *      description="Soft delete JobType",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass JobType data",
     *          @OA\JsonContent(
     *              required={"JobType_id"},
     *              @OA\Property(property="JobType_id", type="integer", format="JobType_id", example="1"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="JobType deleted successfully",
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
    public function softDeleteJobType(Request $request)
    {
        $validation = Validator::make($request->all(), ['jobType_id' => 'required|exists:job_types,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
//        dd('dd');
        $jobType = jobType::find($request->jobType_id);
//        dd('cc');
        dd($jobType);
        if (is_null($jobType)) {
            return $this->ApiResponse(400, 'JobType already deleted');
        }
        $JobType->delete();
        return $this->ApiResponse(200,'JobType deleted successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/Job-types/restore",
     *      operationId="restore specific JobType",
     *      tags={"JobTypes"},
     *      summary="Restore delete JobType",
     *      description="Restore JobType",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass JobType data",
     *          @OA\JsonContent(
     *              required={"JobType_id"},
     *              @OA\Property(property="JobType_id", type="integer", format="JobType_id", example="1"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="JobType restored successfully",
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
    public function restoreJobType(Request $request)
    {
        $validation = Validator::make($request->all(), ['jobType_id' => 'required|exists:job_types,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $JobType = JobType::withTrashed()->find($request->jobType_id);
        if (!is_null($JobType->deleted_at)) {
            $JobType->restore();
            return $this->ApiResponse(200,'JobType restored successfully');
        }
        return $this->ApiResponse(200,'JobType already restored');
    }

}
