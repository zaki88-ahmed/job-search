<?php

namespace App\Http\Controllers\Api\Modules\Jobs;

use App\Http\Controllers\Api\Modules\Categories\Category;
use App\Http\Controllers\Api\Modules\Locations\Location;
use App\Http\Controllers\Api\Modules\Users\User;
use App\Http\Controllers\Controller;
use App\Http\Filter\FilterHelper;
use App\Http\Requests\JobRequest;
use App\Http\Resources\JobAppliedResource;
use App\Http\Resources\JobResource;
use App\Http\Traits\ApiResponseTrait;
use App\Rules\ValidJobStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class JobController extends Controller
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware(['permissions:approve-user'])->only('approveUserJob');
        $this->middleware(['permissions:approve-company'])->only('approveCompanyJob');
        $this->middleware(['permissions:admin-jobs-read'])->only('getAllJobs');
        $this->middleware(['permissions:jobs-read'])->only(['userApplyJob', 'getUserJobs']);
        $this->middleware(['permissions:jobs-create'])->only('createJob');
        $this->middleware(['permissions:jobs-update'])->only('updateJob');
        $this->middleware(['permissions:jobs-delete'])->only(['softDeleteJob', 'restoreJob']);
    }

    /**
     * @OA\Get(
     *      path="/api/jobs",
     *      operationId="Get all jobs",
     *      tags={"Jobs"},
     *      summary="Get list of all jobs",
     *      description="Returns list of all jobs",
     *      security={ {"sanctum": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="All Jobs",
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
    public function getAllJobs()
    {
        $jobs = Job::with(['company', 'category', 'location', 'type'])->paginate(10);
        return $this->apiResponse(200,'All Jobs',null,  $jobs);
    }

    /**
     * @OA\Get(
     *      path="/api/jobs/search",
     *      operationId="show filtered jobs",
     *      tags={"Jobs"},
     *      summary="show filtered job",
     *      description="show filtered job",
     *      @OA\Parameter(
     *          name="city",
     *          in="query",
     *          required=false,
     *          description="Enter city name",
     *      ),
     *      @OA\Parameter(
     *          name="category",
     *          in="query",
     *          required=false,
     *          description="Enter category name",
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Jobs filtered",
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
    public function filterJobs(Request $request)
    {
//        $jobLocations = Location::where('city', 'like', "%".request('city')."%")->get();
//        $jobCategories = Category::where('name', 'like', "%".request('category')."%")->first();
//
//        dd($jobLocations);
////        dd($jobCategories->id);
//
//        $jobs = Job::where('category_id', $jobCategories ? $jobCategories->id : NULL)
//            ->Where('location_id', $jobLocations ? $jobLocations->id : NULL)->paginate(10);
//        dd($jobs);

        $filter_conditions = $request->only(['keyword', 'category_ids', 'store_ids']);
        $query = FilterHelper::apply(Job::query(), $filter_conditions);
        $jobs = $query->get();
        return $this->apiResponse(200,'Filtered Jobs',null, $jobs);
    }

    /**
     * @OA\Get(
     *      path="/api/jobs/show",
     *      operationId="show specific job",
     *      tags={"Jobs"},
     *      summary="show specific job",
     *      description="show specific job",
     *      @OA\Parameter(
     *          name="job_id",
     *          in="query",
     *          required=true,
     *          description="Enter job id",
     *          ),
     *      @OA\Response(
     *          response=200,
     *          description="Job details",
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
     *  )
     */

    public function getJobById(Request $request)
    {
         $validation = Validator::make($request->all(), ['job_id' => 'required|exists:jobs,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $job = Job::where('id', $request->job_id)->first();
        return $this->ApiResponse(200, 'Job details', null, JobResource::make($job));
    }

    /**
     * @OA\Post(
     *      path="/api/jobs/create",
     *      operationId="create new job",
     *      tags={"Jobs"},
     *      summary="Create new job",
     *      description="Add new job",
     *      security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass job credentials",
     *          @OA\JsonContent(
     *              required={"title", "salary_range", "requirements", "description", "years_of_experience", "category_id", "company_id", "location_id", "job_type_id"},
     *              @OA\Property(property="title", type="string", format="title", example="job"),
     *              @OA\Property(property="salary_range", type="string", format="salary_range", example="1000 L.E - 2000 L.E"),
     *              @OA\Property(property="requirements", type="string", format="requirements", example="job requirements"),
     *              @OA\Property(property="description", type="string", format="description", example="job description"),
     *              @OA\Property(property="years_of_experience", type="string", format="years_of_experience", example="less than 1 year"),
     *              @OA\Property(property="category_id", type="integer", format="category_id", example="1"),
     *              @OA\Property(property="company_id", type="integer", format="company_id", example="1"),
     *              @OA\Property(property="location_id", type="integer", format="location_id", example="1"),
     *              @OA\Property(property="job_type_id", type="integer", format="job_type_id", example="1")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Job created successfully",
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
    public function createJob(JobRequest $request)
    {
        $user = auth('sanctum')->user();
        $companyExists = User::whereHas('role', function($query) use ($user) {
            return $query->where('name', 'company');
        })->where('id', $user->id)->first();
        if (is_null($companyExists)) {
            return $this->ApiResponse(401,'Unauthorized');
        }
        $data['company_id'] = $user->id;
        $data += $request->all();
        Job::create($data);
        return $this->ApiResponse(200,'Job created successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/jobs/update",
     *      operationId="update job",
     *      tags={"Jobs"},
     *      summary="Update job",
     *      description="Edit job",
     *      security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass job credentials",
     *          @OA\JsonContent(
     *              required={"job_id", "title", "salary_range", "requirements", "description", "years_of_experience", "category_id", "company_id", "location_id", "job_type_id"},
     *              @OA\Property(property="job_id", type="integer", format="job_id", example="1"),
     *              @OA\Property(property="title", type="string", format="title", example="job"),
     *              @OA\Property(property="salary_range", type="string", format="salary_range", example="1000 L.E - 2000 L.E"),
     *              @OA\Property(property="requirements", type="string", format="requirements", example="job requirements"),
     *              @OA\Property(property="description", type="string", format="description", example="job description"),
     *              @OA\Property(property="years_of_experience", type="integer", format="years_of_experience", example="less than 1 year"),
     *              @OA\Property(property="category_id", type="integer", format="category_id", example="1"),
     *              @OA\Property(property="company_id", type="integer", format="company_id", example="1"),
     *              @OA\Property(property="location_id", type="integer", format="location_id", example="1"),
     *              @OA\Property(property="job_type_id", type="integer", format="job_type_id", example="1")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Job updated successfully",
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
    public function updateJob(JobRequest $request)
    {
        if (is_null($request->job_id)) {
            return $this->ApiResponse(400,'The job_id field is required');
        }
        $user = auth('sanctum')->user();
        $companyExists = Job::where([['id', $request->job_id], ['company_id', $user->id]])->first();
        if (is_null($companyExists)) {
            return $this->ApiResponse(401,'Unauthorized');
        }
        Job::find($request->job_id)->update($request->all());
        return $this->ApiResponse(200,'Job updated successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/jobs/delete",
     *      operationId="delete specific job",
     *      tags={"Jobs"},
     *      summary="Soft delete job",
     *      description="Soft delete job",
     *      security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass job credentials",
     *          @OA\JsonContent(
     *              required={"job_id"},
     *              @OA\Property(property="job_id", type="integer", format="job_id", example="1"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Job deleted successfully",
     *      ),
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
     *  )
     */
    public function softDeleteJob(Request $request)
    {
        $validation = Validator::make($request->all(), ['job_id' => 'required|exists:jobs,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }

        $user = auth('sanctum')->user();
        $companyExists = Job::where([['id', $request->job_id], ['company_id', $user->id]])->first();
        if (is_null($companyExists)) {
            return $this->ApiResponse(401,'Unauthorized');
        }

        $job = Job::find($request->job_id);
        if (is_null($job)) {
            return $this->ApiResponse(400, 'Job already deleted');
        }
        $job->delete();
        return $this->ApiResponse(200,'Job deleted successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/jobs/restore",
     *      operationId="restore specific job",
     *      tags={"Jobs"},
     *      summary="Restore delete job",
     *      description="Restore job",
     *      security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass job credentials",
     *          @OA\JsonContent(
     *              required={"job_id"},
     *              @OA\Property(property="job_id", type="integer", format="job_id", example="1"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Job restored successfully",
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
    public function restoreJob(Request $request)
    {
        $validation = Validator::make($request->all(), ['job_id' => 'required|exists:jobs,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }

        $user = auth('sanctum')->user();
//        dd($user);
        $companyExists = Job::withTrashed()->where([['id', $request->job_id], ['company_id', $user->id]])->first();
//        $companyExists = Job::where([['id', $request->job_id]])->first();
//        dd($companyExists);
        if (is_null($companyExists)) {
            return $this->ApiResponse(401,'Unauthorized');
        }

        $job = Job::withTrashed()->find($request->job_id);
        if (!is_null($job->deleted_at)) {
            $job->restore();
            return $this->ApiResponse(200,'Job restored successfully');
        }
        return $this->ApiResponse(200,'Job already restored');
    }

    /**
     * @OA\Post(
     *      path="/api/jobs/apply",
     *      operationId="apply new job",
     *      tags={"Jobs"},
     *      summary="apply new job",
     *      description="Apply new job",
     *      security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 allOf={
     *                     @OA\Schema(ref="#components/schemas/item"),
     *                     @OA\Schema(
     *                     required={"job_id"},
     *                     @OA\Property(property="job_id", type="integer", format="job_id", example="1"),
     *                     @OA\Property(description="resume",property="resume",type="string", format="binary")
     *                 )
     *                }
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Job applied successfully",
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
    public function userApplyJob(Request $request){

        $validation = Validator::make($request->all(), [
            'job_id' => ['required', 'exists:jobs,id', new ValidJobStatus()],
            'resume' => 'nullable|mimes:pdf'
        ]);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $user = auth('sanctum')->user();
        if ((is_null($user->details) || is_null($user->details->resume)) && is_null($request->resume)) {
            return $this->ApiResponse(400, 'The resume field is required.');
        }
        if ($user->details && $user->details->resume) {
            $resume = $user->details->resume;
        }
        if ($request->resume) {
            $file = $request->file('resume');
            $filename = time() . '.' . $request->file('resume')->extension();
            $filePath = public_path() . '/uploads/resumes/';
            $file->move($filePath, $filename);
            $resume =  $filename;
        }
        $data = [
            'job_id'  => $request->job_id,
            'resume'  => $resume
        ];
        $user->jobs()->attach($user->id, $data);
        return $this->apiResponse(200,'Done');
    }

    /**
     * @OA\Get(
     *      path="/api/jobs/apply",
     *      operationId="Get user jobs",
     *      tags={"Jobs"},
     *      summary="Get list of user jobs",
     *      description="Returns list of user jobs",
     *      security={ {"sanctum": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="All User Jobs",
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
    public function getUserJobs()
    {
        $user = auth('sanctum')->user();
        $jobs = $user->jobs;
        return $this->apiResponse(200,'All User Jobs', NULL, JobAppliedResource::collection($jobs));
    }

    /**
     * @OA\Post(
     *      path="/api/jobs/companies/approve",
     *      operationId="Approve company job",
     *      tags={"Jobs"},
     *      summary="Approve company job",
     *      description="Approve company job",
     *      security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass approve company job credentials",
     *          @OA\JsonContent(
     *              required={"job_id", "is_published"},
     *              @OA\Property(property="job_id", type="string", format="job_id", example="1"),
     *              @OA\Property(property="is_published", type="string", format="is_published", example="accepted")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Company Job updated successfully",
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
    public function approveCompanyJob(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'job_id' => 'required|exists:jobs,id',
            'is_published' => 'required|in:pending,rejected,under review,accepted'
        ]);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        Job::find($request->job_id)->update(['is_published' => $request->is_published]);
        return $this->apiResponse(200,'Company Job updated successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/jobs/users/approve",
     *      operationId="Approve user Job",
     *      tags={"Jobs"},
     *      summary="Approve user job",
     *      description="Approve user job",
     *      security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass approve user job credentials",
     *          @OA\JsonContent(
     *              required={"job_id", "user_id", "status"},
     *              @OA\Property(property="job_id", type="string", format="job_id", example="1"),
     *              @OA\Property(property="user_id", type="string", format="user_id", example="1"),
     *              @OA\Property(property="status", type="string", format="status", example="accepted")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User Job updated successfully",
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
    public function approveUserJob(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'job_id' => 'required|exists:jobs,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:pending,rejected,under review,accepted'
        ]);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $company = auth('sanctum')->user();
        $companyExists = Job::whereHas('users', function($query) use ($company) {
            return $query->where('company_id', $company->id);
        })->where('id', $request->job_id)->first();

        if (is_null($companyExists)) {
            return $this->ApiResponse(401, 'Unauthorized');
        }

        $user = User::find($request->user_id);
        $user->jobs()->updateExistingPivot($request->job_id, [
            'status' => $request->status
        ]);
        return $this->apiResponse(200,'User Job updated successfully');
    }
}
