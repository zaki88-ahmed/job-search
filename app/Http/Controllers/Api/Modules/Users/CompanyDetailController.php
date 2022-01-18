<?php

namespace App\Http\Controllers\Api\Modules\Users;

use App\Http\Resources\CompanyDetailsResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CompanyDetailController extends Controller
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware(['permissions:companies-create,companies-update'])->only('updateOrCreateCompanyDetails');
    }
    /**
     * @OA\Get(
     *      path="/api/companies/details",
     *      operationId="show specific company details",
     *      tags={"Company Details"},
     *      summary="show specific company details",
     *      description="show specific company details",
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
     *      @OA\Response(
     *          response=400,
     *          description="Validation Error"
     *      )
     *  )
     */
    public function getCompanyDetails()
    {
        $company = User::where('id', request('company_id'))->first();

        $companyExists = User::whereHas('role', function($query) use ($company) {
            return $query->where('name', 'company');
        })->where('role_id', $company->role_id)->first();
        if (is_null($companyExists)) {
            return $this->apiResponse(404, 'This is not a company');
        }
        $companyDetails = CompanyDetail::where('company_id' , $company->id)->first();
        return $this->apiResponse(200, 'Company details', NULL, CompanyDetailsResource::make($companyDetails));
    }

    /**
     * @OA\Post(
     *      path="/api/companies/details/create-or-update",
     *      operationId="create or update company detail",
     *      tags={"Company Details"},
     *      summary="Create Or Update your company details",
     *      description="Add Or Edit your company details",
     *      security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 allOf={
     *                     @OA\Schema(ref="#components/schemas/item"),
     *                     @OA\Schema(
     *                     required={"site", "size", "job_numbers", "description"},
     *                     @OA\Property(property="site", type="string", format="site", example="new_site"),
     *                     @OA\Property(property="size", type="integer", format="size", example="11"),
     *                     @OA\Property(property="job_numbers", type="integer", format="job_numbers", example="3"),
     *                     @OA\Property(property="description", type="string", format="description", example="comp_description"),
     *                     @OA\Property(description="logo",property="logo",type="string", format="binary")
     *                  )
     *                 }
     *               )
     *             )
     *           ),
     *      @OA\Response(
     *          response=200,
     *          description="detail created successfully",
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
     *          description="Validation Errors"
     *      )
     *     )
     */
    public function updateOrCreateCompanyDetails(Request $request) {
//        dd('ss');
        $validation = Validator::make($request->all(), [
            'site'        => 'required',
            'description'        => 'required|string',
            'logo'        => 'nullable|image',
            'size'        => 'required',
            'job_numbers' => 'required',
            'company_id'  => 'nullable|exists:users,id'
        ]);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Errors', $validation->errors());
        }
//        dd('ss');
        $user = auth('sanctum')->user();
        if ($user->role->name != "Company") {
            return $this->ApiResponse(401,'Unauthorized');
        }
//        $companyExists = User::whereHas('role', function($query) use ($user) {
//            return $query->where('name', 'Company');
//        })->where('id', $user->id)->first();
        $companyExists = CompanyDetail::whereHas('company', function($query) use ($user) {
            return $query->whereHas('role', function($query) use ($user) {
                return $query->where('name', 'Company');
            });
        })->where('company_id', $user->id)->first();

        if ($request->hasFile('logo')) {
            if ($companyExists) {
                Storage::disk('uploads')->delete('logos/' . $companyExists->logo);
            }
            $file = $request->file('logo');
            $filename = time() . '.' . $request->file('logo')->extension();
            $filePath = public_path() . '/uploads/logos/';
            $file->move($filePath, $filename);

            $request->logo =  $filename;
        }else {
            if ($companyExists) {
                $request->logo = $companyExists->logo;
            }
        }
//        if ($companyExists) {
//            UserDetail::where('company_id' , $user->id)->update([
//                'name'           => $request->name,
//                'marital_status'   => $request->marital_status,
//                'military_status' => $request->military_status,
//                'nationality'      => $request->nationality,
//                'resume'           => $request->resume
//            ]);
//            $companyDetails = CompanyDetail::where('company_id' , $user->id);
//        dd($companyDetails);
        if ($companyExists) {
            $companyExists->update([
                'company_id'     => $user->id,
                'site'        => $request->site,
                'size'        => $request->size,
                'description'        => $request->description,
                'job_numbers' => $request->job_numbers,
                'logo'        => $request->logo ?? NULL
            ]);
//            dd($request->all());
//            dd($companyDetails);
            return $this->ApiResponse(200,'your company details updated successfully', null, $companyExists);
        }else {
            $companyExists = CompanyDetail::create([
                'company_id'     => $user->id,
                'site'        => $request->site,
                'size'        => $request->size,
                'description'        => $request->description,
                'job_numbers' => $request->job_numbers,
                'logo'        => $request->logo ?? NULL
            ]);
        }
        return $this->ApiResponse(200,'your company details added successfully', null, $companyExists);
    }
}
