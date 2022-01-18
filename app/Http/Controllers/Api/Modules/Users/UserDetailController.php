<?php

namespace App\Http\Controllers\Api\Modules\Users;

use App\Http\Traits\UploadResumeTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserDetailController extends Controller
{
    use ApiResponseTrait;
    use UploadResumeTrait;

    public function __construct()
    {
//        $this->middleware(['permissions:users-read'])->only('getUserDetails');
//        $this->middleware(['permissions:users-create,users-update'])->only('updateOrCreateUserDetails');
    }
    /**
     * @OA\Get(
     *      path="/api/users/details",
     *      operationId="get user details",
     *      tags={"User Details"},
     *      summary="Get user details",
     *      description="Returns user details",
     *      security={ {"sanctum": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="user details",
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
    public function getUserDetails() {
        $user = auth('sanctum')->user();
        $userDetails = UserDetail::where('user_id' , $user->id)->first();

        return $this->apiResponse(200, 'user details', null, $userDetails);

    }

    /**
     * @OA\Post(
     *      path="/api/users/details/create-or-update",
     *      operationId="create or update detail",
     *      tags={"User Details"},
     *      summary="Create Or Update your User details",
     *      description="Add Or Edit your User details",
     *      security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 allOf={
     *                     @OA\Schema(ref="#components/schemas/item"),
     *                     @OA\Schema(
     *                     required={"gender", "marital_status", "military_status", "nationality"},
     *                     @OA\Property(property="gender", type="string", format="gender", example="male"),
     *                     @OA\Property(property="nationality", type="string", format="nationality", example="egyption"),
     *                     @OA\Property(property="marital_status", type="string", format="marital_status", example="single"),
     *                     @OA\Property(property="military_status", type="string", format="military_status", example="exemption"),
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
    public function updateOrCreateUserDetails(Request $request) {
        $validation = Validator::make($request->all(), [
            'gender'             => 'required|in:male,female',
            'marital_status'     => 'required|in:single,married,widowed,divorced,separated',
            'military_status'   => 'required|in:exemption,completed,postponed,currently serving',
            'nationality'        => 'required|string|max:255',
            'resume'             => 'nullable|mimes:pdf'
        ]);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }


//        dd($request->all());
        $user = auth('sanctum')->user();
//        dd($user->id);
        $userExists = UserDetail::all();
//        dd($userExists);
        $userExists = $userExists->where('user_id', $user->id)->first();
//        dd($userExists->id);
//        dd($userExists);
//        dd($userExists->resume);
//        dd($userExists->marital_status);
        if ($request->hasFile('resume')) {
//            dd($userExists->id);
//            dd($userExists->resume);
            if ($userExists) {
//                dd($userExists->id);
//                Storage::disk('uploads')->delete('resumes/' . $userExists->resume);
//                Storage::disk('uploads')->delete('logos/' . $companyExists->logo);
//                dd($userExists->id);
//                dd('cc');
//                dd($userExists->id);
                dd($userExists->resume);
                $this->deleteResume('resumes/' . $userExists->resume);
                $this->uploadResume($request, 'resumes');
                dd($userExists->id);
            }
            dd($userExists->id);
//            $file = $request->file('resume');
//            $filename = time() . '.' . $request->file('resume')->extension();
//            $filePath = public_path() . '/uploads/resumes/';
//            $file->move($filePath, $filename);
//
//            $request->resume =  $filename;
//            dd($userExists->id);
//            dd('mm');
            $this->uploadResume($request, 'resumes');
        }else {
//            dd('vv');
            if ($userExists) {
//                dd('ff');
                $request->resume = $userExists->resume;
            }
        }
//        dd($userExists);
        if ($userExists) {
//            dd('cc');
//            dd($userExists);
            $userExists->update([
                'user_id'     => $user->id,
                'gender'           => $request->gender,
                'marital_status'   => $request->marital_status,
                'military_status' => $request->military_status,
                'nationality'      => $request->nationality,
                'resume'           => $request->resume ?? NULL,
            ]);
//            dd($userExists->user_id);
//            return ($userExists);
//            return  response()->json($userExists);
//            dd($user->id);
            $user = User::all();
            $user = $user->where('id', $user->id)->with('userDetail')->first();
//            return response()->json($user);
            return $this->ApiResponse(200,'your details updated successfully', null, $user);
        }else {
//            dd('dd');
            $userExists = UserDetail::create([
                'user_id'          => $user->id,
                'gender'           => $request->gender,
                'marital_status'   => $request->marital_status,
                'military_status' => $request->military_status,
                'nationality'      => $request->nationality,
                'resume'           => $request->resume ?? NULL,
            ]);
//            dd('mmmm');
//            dd($userExists);
//            return  response()->json($userExists);
            $user = User::where('id', $user->id)->with('userDetail')->first();
//            dd($user->id);
            return $this->ApiResponse(200,'your details added successfully', null, $user);
        }

    }
}
