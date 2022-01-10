<?php

namespace App\Http\Controllers\Api\Modules\Categories;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class CategoryController extends Controller
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware(['permissions:categories-read'])->only(['getAllCategories', 'getCategoryById']);
        $this->middleware(['permissions:categories-create'])->only('createCategory');
        $this->middleware(['permissions:categories-update'])->only('updateCategory');
        $this->middleware(['permissions:categories-delete'])->only(['softDeleteCategory', 'restoreCategory']);
    }

     /**
     * @OA\Get(
     *      path="/api/categories",
     *      operationId="Get all categories",
     *      tags={"Categories"},
     *      summary="Get list of all categories",
     *      description="Returns list of all categories",
     *      @OA\Response(
     *          response=200,
     *          description="All categories",
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
    public function getAllCategories()
    {
        $categories = category::get();
        return $this->apiResponse(200,'All categories',null, CategoryResource::collection($categories));
    }

    /**
     * @OA\Post(
     *      path="/api/category/add",
     *      operationId="create new Category",
     *      tags={"Categories"},
     *      summary="Create new Category",
     *      description="Add new Category",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Enter Category data",
     *          @OA\JsonContent(
     *              required={"name"},
     *              @OA\Property(property="name", type="string", format="name", example="Electronic"),
     *              @OA\Property(property="parent_id", type="integer", format="parent_id", example="1")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Category created successfully",
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
    public function createCategory (Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required',
            'parent_id' => 'nullable'
        ]);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }

        Category::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id ?? NULL
        ]);
        return $this->ApiResponse(200,'Category created successfully');
    }

    /**
     * @OA\Get(
     *      path="/api/category/show",
     *      operationId="show specific category",
     *      tags={"Categories"},
     *      summary="show specific category",
     *      description="show specific category",
     *   @OA\Parameter(
     *    name="category_id",
     *    in="query",
     *    required=true,
     *    description="Enter category id",
     *    ),
     *      @OA\Response(
     *          response=200,
     *          description="category details",
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

    public function getCategoryById(Request $request)
    {
        $validation = Validator::make($request->all(), ['category_id' => 'required|exists:categories,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $category = Category::where('id', $request->category_id)->with(['parentCategory', 'jobs'])->first();
        return $this->ApiResponse(200, 'Category details', null, CategoryResource::collection($category));
    }

    /**
     * @OA\Post(
     *      path="/api/category/update",
     *      operationId="update Category",
     *      tags={"Categories"},
     *      summary="Update Category",
     *      description="Edit Category",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Enter Category data",
     *          @OA\JsonContent(
     *              required={"category_id","name"},
     *              @OA\Property(property="category_id", type="integer", format="category_id", example="1"),
     *              @OA\Property(property="name", type="string", format="name", example="Electronic"),
     *              @OA\Property(property="parent_id", type="integer", format="parent_id", example="1")
     *      ),
     * ),
     *      @OA\Response(
     *          response=200,
     *          description="Category updated successfully",
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
    public function updateCategory(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'category_id'  => 'required|exists:categories,id',
            'name'         => 'required'
        ]);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        Category::find($request->category_id)->update([
            'name'       => $request->name,
            'parent_id'  => $request->parent_id
        ]);
        return $this->ApiResponse(200,'Category updated successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/category/delete",
     *      operationId="delete specific category",
     *      tags={"Categories"},
     *      summary="Soft delete category",
     *      description="Soft delete category",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass category data",
     *          @OA\JsonContent(
     *              required={"category_id"},
     *              @OA\Property(property="category_id", type="integer", format="category_id", example="1"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="category deleted successfully",
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
    public function softDeleteCategory(Request $request)
    {
        $validation = Validator::make($request->all(), ['category_id' => 'required|exists:categories,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }
        $category = category::find($request->category_id);
        if (is_null($category)) {
            return $this->ApiResponse(400, 'category already deleted');
        }
        $category->delete();
        return $this->ApiResponse(200,'category deleted successfully');
    }

    /**
     * @OA\Post(
     *      path="/api/category/restore",
     *      operationId="restore specific category",
     *      tags={"Categories"},
     *      summary="Restore delete category",
     *      description="Restore category",
     *     security={ {"sanctum": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass category data",
     *          @OA\JsonContent(
     *              required={"category_id"},
     *              @OA\Property(property="category_id", type="integer", format="category_id", example="1"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="category restored successfully",
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
    public function restoreCategory(Request $request)
    {
        $validation = Validator::make($request->all(), ['category_id' => 'required|exists:categories,id']);
        if ($validation->fails()) {
            return $this->ApiResponse(400, 'Validation Error', $validation->errors());
        }

        $category = category::withTrashed()->find($request->category_id);
        if (!is_null($category->deleted_at)) {
            $category->restore();
            return $this->ApiResponse(200,'category restored successfully');
        }
        return $this->ApiResponse(200,'category already restored');
    }

}
