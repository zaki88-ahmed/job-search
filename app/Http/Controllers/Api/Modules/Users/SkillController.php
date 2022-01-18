<?php

namespace App\Http\Controllers\Api\Modules\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ApiResponseTrait;

class SkillController extends Controller
{
    use ApiResponseTrait ;

    public function __construct()
    {
        $this->middleware(['permissions:users-read'])->only(['getAllSkills']);
        $this->middleware(['permissions:users-create'])->only('createSkill');
        $this->middleware(['permissions:users-update'])->only('updateSkill');
        $this->middleware(['permissions:users-delete'])->only('deleteSkill');
    }
    /**
     * @OA\Get(
     *      path="/api/skills",
     *      operationId="get skills list",
     *      tags={"Skills"},
     *      summary="Get list of skills",
     *      description="Returns list of skills",
     *     security={ {"sanctum": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="All skills",
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

    public function getAllSkills() {
        $skills = Skill::orderBy('id', 'DESC')->get();

        return $this->apiResponse(200, 'All Skills', null, $skills);
    }
    /**
     * @OA\Post(
     *      path="/api/skills/create",
     *      operationId="create new skill",
     *      tags={"Skills"},
     *      summary="Create new skill",
     *      description="Add new skill",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass skill credentials",
     *          @OA\JsonContent(
     *              required={"name", "years_of_experience", "justification"},
     *              @OA\Property(property="name", type="string", format="name", example="skill"),
     *              @OA\Property(property="years_of_experience", type="string", format="years_of_experience", example="1-3 years"),
     *              @OA\Property(property="justification", type="string", format="justification", example="test"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="skill created successfully",
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
    public function createSkill(Request $request) {
        $validation = Validator::make($request->all(), [
            'name'                  => 'required|string|max:255',
            'years_of_experience'   => 'required|in:less than 1 year,1-3 years,3-5 years,5-7 years,more than 7 years',
            'justification'         => 'required|string',
        ]);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $user = auth('sanctum')->user();
//        dd($user->id);
        $skill = Skill::create([
            'name'                  => $request->name,
            'years_of_experience'   => $request->years_of_experience,
            'justification'         => $request->justification
        ]);
//        dd($skill->id);
        $data = [
            'skill_id'  => $skill->id,
        ];
//        dd($data);

//        dd($user);
//        $skill->users()->sync($user);
        $user->skills()->attach($user->id, $data);
//        dd($user->skills());
        return $this->ApiResponse(200,'skill created successfully');
    }

     /**
     * @OA\Post(
     *      path="/api/skills/update",
     *      operationId="update skill",
     *      tags={"Skills"},
     *      summary="Update skill",
     *      description="Edit skill",
      *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass skill credentials",
     *          @OA\JsonContent(
     *              required={"skill_id", "name", "years_of_experinces", "justification"},
     *              @OA\Property(property="skill_id", type="integer", format="skill_id", example="1"),
     *              @OA\Property(property="name", type="string", format="name", example="skill"),
     *              @OA\Property(property="years_of_experience", type="string", format="years_of_experience", example="1-3 years"),
     *              @OA\Property(property="justification", type="string", format="justification", example="test"),
     *      ),
     * ),
     *      @OA\Response(
     *          response=200,
     *          description="skill updated successfully",
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
    public function updateSkill(Request $request) {
        $validator = Validator::make($request->all(), [
            'skill_id'              => 'required|exists:skills,id',
            'name'                  => 'required|string|max:255',
            'years_of_experience'   => 'required|in:less than 1 year,1-3 years,3-5 years,5-7 years,more than 7 years',
            'justification'         => 'required|string'
        ]);
        if ($validator->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validator->errors());
        }
        $user = auth('sanctum')->user();
        $skill = Skill::where('id', $request->skill_id)->first();
        if (is_null($skill)) {
            return $this->ApiResponse(400, 'Skill not exist');
        }

        $pivotRow = $user->skills()->where('skill_id', $skill->id)->first();
//        dd($user->id);
//        dd($skill->id);
//        dd($pivotRow);

        if($pivotRow){
            $skill->update([
                'name'                  => $request->name,
                'years_of_experience'   => $request->years_of_experience,
                'justification'         => $request->justification
            ]);
            $data = [
                'skill_id'  => $skill->id,
            ];
            $user->skills()->sync($data);
            return $this->apiResponse(200,'skill updated successfully');

        }

        return $this->apiResponse(200,'User do not have skill');
    }

    /**
     * @OA\Post(
     *      path="/api/skills/delete",
     *      operationId="delete specific skill",
     *      tags={"Skills"},
     *      summary="Soft delete skill",
     *      description="Soft delete skill",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass skill credentials",
     *          @OA\JsonContent(
     *              required={"skill_id"},
     *              @OA\Property(property="skill_id", type="integer", format="skill_id", example="1"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="skill deleted successfully",
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
    public function deleteSkill(Request $request) {
        $validator = Validator::make($request->all(), ['skill_id'=> 'required|exists:skills,id']);
        if($validator->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validator->errors());
        }
        $skill = Skill::where('id', $request->skill_id)->first();
        if (is_null($skill)) {
            return $this->ApiResponse(400, 'skill already deleted');
        }
        $skill->delete();
        return $this->apiResponse(200,'skill deleted successfully');
    }
}
