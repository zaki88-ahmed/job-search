<?php

namespace App\Http\Controllers\Api\Modules\Locations;

use App\Http\Controllers\Controller;
use App\Http\Resources\LocationResource;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware(['permissions:locations-create'])->only('createLocation');
        $this->middleware(['permissions:locations-update'])->only('updateLocation');
        $this->middleware(['permissions:locations-delete'])->only(['softDeleteLocation', 'restoreLocation']);
    }
    /**
     * @OA\Get(
     *      path="/api/locations",
     *      operationId="Get all locations",
     *      tags={"Locations"},
     *      summary="Get list of all locations",
     *      description="Returns list of all locations",
     *      @OA\Response(
     *          response=200,
     *          description="All Locations",
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
    public function getAllLocations()
    {
        $locations = Location::get();
        return $this->apiResponse(200,'All Locations',NULL, LocationResource::collection($locations));
    }

    /**
     * @OA\Post(
     *      path="/api/locations/create",
     *      operationId="create new location",
     *      tags={"Locations"},
     *      summary="Create new location",
     *      description="Add new location",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Enter location data",
     *          @OA\JsonContent(
     *              required={"country", "city"},
     *              @OA\Property(property="country", type="string", format="country", example="Egypt"),
     *              @OA\Property(property="city", type="string", format="city", example="Cairo")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Location created successfully",
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
    public function createLocation(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'country' => 'required',
            'city'    => 'required'
        ]);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Errors', $validation->errors());
        }
        Location::create($request->all());
        return $this->ApiResponse(200,'Location created successfully');
    }

    /**
     * @OA\Get(
     *      path="/api/locations/show",
     *      operationId="show specific location",
     *      tags={"Locations"},
     *      summary="show specific location",
     *      description="show specific location",
     *   @OA\Parameter(
     *    name="location_id",
     *    in="query",
     *    required=true,
     *    description="Enter location id",
     *    ),
     *      @OA\Response(
     *          response=200,
     *          description="Location details",
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
    public function getLocationById(Request $request)
    {
//        dd('dd');
        $validation = Validator::make($request->all(), ['location_id' => 'required|exists:locations,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
//        dd('ss');
//        $location = Location::where('id', $request->location_id)->first();
        $location = Location::find($request->location_id);
//        dd($location);
        return $this->ApiResponse(200, 'Location details', null, LocationResource::make($location));
    }
    /**
     * @OA\Post(
     *      path="/api/locations/update",
     *      operationId="update location",
     *      tags={"Locations"},
     *      summary="Update location",
     *      description="Edit location",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Enter location data",
     *          @OA\JsonContent(
     *              required={"location_id", "country", "city"},
     *              @OA\Property(property="location_id", type="integer", format="location_id", example="1"),
     *              @OA\Property(property="country", type="string", format="country", example="Egypt"),
     *              @OA\Property(property="city", type="string", format="city", example="Cairo")
     *      ),
     * ),
     *      @OA\Response(
     *          response=200,
     *          description="Location updated successfully",
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
    public function updateLocation(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'location_id'  => 'required|exists:locations,id',
            'country' => 'required',
            'city' => 'required'
        ]);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Errors', $validation->errors());
        }
        Location::find($request->location_id)->update($request->all());
        return $this->ApiResponse(200,'Location updated successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/locations/delete",
     *      operationId="delete specific location",
     *      tags={"Locations"},
     *      summary="Soft delete location",
     *      description="Soft delete location",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass location data",
     *          @OA\JsonContent(
     *              required={"location_id"},
     *              @OA\Property(property="location_id", type="integer", format="location_id", example="1"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Location deleted successfully",
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
    public function softDeleteLocation(Request $request)
    {
        $validation = Validator::make($request->all(), ['location_id' => 'required|exists:locations,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $location = Location::find($request->location_id);
        if (is_null($location)) {
            return $this->ApiResponse(400, 'Location already deleted');
        }
        $location->delete();
        return $this->ApiResponse(200,'Location deleted successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/locations/restore",
     *      operationId="restore specific location",
     *      tags={"Locations"},
     *      summary="Restore delete location",
     *      description="Restore location",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass location data",
     *          @OA\JsonContent(
     *              required={"location_id"},
     *              @OA\Property(property="location_id", type="integer", format="location_id", example="1"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="location restored successfully",
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
    public function restoreLocation(Request $request)
    {
        $validation = Validator::make($request->all(), ['location_id' => 'required|exists:locations,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $location= Location::withTrashed()->find($request->location_id);
        if (!is_null($location->deleted_at)) {
            $location->restore();
            return $this->ApiResponse(200,'Location restored successfully');
        }
        return $this->ApiResponse(200,'Location already restored');
    }
}
