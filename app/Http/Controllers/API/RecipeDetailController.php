<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RecipeDetail;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

// Controller for managing RecipeDetail resources via API
class RecipeDetailController extends Controller
{
    /**
     * Display a paginated listing of the recipe detail resources.
     * Supports sorting and pagination via query parameters.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'rd_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 10);
        
        $query = RecipeDetail::with('recipe')->orderBy($sortBy, $sortOrder);
        
        $recipeDetails = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Recipe details retrieved successfully',
            'data' => $recipeDetails
        ]);
    }

    /**
     * Get recipe detail by recipe ID.
     * Returns the detail for a specific recipe if it exists.
     *
     * @param  int  $recipeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByRecipeId($recipeId)
    {
        $recipe = Recipe::find($recipeId);
        
        if (!$recipe) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found'
            ], 404);
        }
        
        $recipeDetail = RecipeDetail::where('rd_id_recipe', $recipeId)->first();
        
        if (!$recipeDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe detail not found for this recipe'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Recipe detail retrieved successfully',
            'data' => $recipeDetail
        ]);
    }

    /**
     * Store a newly created recipe detail resource in storage.
     * Handles validation, ensures only one detail per recipe, and sets created_by using employee_id or user_id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rd_id_recipe' => 'required|integer|exists:pazar_recipe,r_id',
            'rd_desc_id' => 'nullable|string',
            'rd_desc_en' => 'nullable|string',
            'rd_ingredients_id' => 'nullable|string',
            'rd_ingredients_en' => 'nullable|string',
            'rd_cook_id' => 'nullable|string',
            'rd_cook_en' => 'nullable|string',
            'rd_link_youtube' => 'nullable|string|max:255',
            'user_id' => 'nullable|integer',
            'employee_id' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Check if recipe detail already exists for this recipe
        $existingDetail = RecipeDetail::where('rd_id_recipe', $request->rd_id_recipe)->first();
        if ($existingDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe detail already exists for this recipe',
                'data' => $existingDetail
            ], 422);
        }
        
        // Check if employee_id is provided directly
        if ($request->has('employee_id')) {
            $employeeId = $request->input('employee_id');
        } else {
            // Use the user_id from request if available, otherwise try auth()->id()
            $userId = $request->input('user_id') ?? auth()->id();
            
            // Get employee_id from User model
            $user = User::find($userId);
            $employeeId = $user ? $user->u_employee_id : (string)$userId;
        }
        
        $data = $request->all();
        $data['rd_created_by'] = $employeeId;
        
        // Remove user_id and employee_id from the data as they're not columns in the recipe_detail table
        if (isset($data['user_id'])) {
            unset($data['user_id']);
        }
        
        if (isset($data['employee_id'])) {
            unset($data['employee_id']);
        }
        
        $recipeDetail = RecipeDetail::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Recipe detail created successfully',
            'data' => $recipeDetail
        ], 201);
    }

    /**
     * Display the specified recipe detail resource by ID, including recipe relationship.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $recipeDetail = RecipeDetail::with('recipe')->find($id);
        
        if (!$recipeDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe detail not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Recipe detail retrieved successfully',
            'data' => $recipeDetail
        ]);
    }

    /**
     * Update the specified recipe detail resource in storage.
     * Handles validation and sets updated_by using employee_id or user_id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $recipeDetail = RecipeDetail::find($id);
        
        if (!$recipeDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe detail not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'rd_desc_id' => 'nullable|string',
            'rd_desc_en' => 'nullable|string',
            'rd_ingredients_id' => 'nullable|string',
            'rd_ingredients_en' => 'nullable|string',
            'rd_cook_id' => 'nullable|string',
            'rd_cook_en' => 'nullable|string',
            'rd_link_youtube' => 'nullable|string|max:255',
            'user_id' => 'nullable|integer',
            'employee_id' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Check if employee_id is provided directly
        if ($request->has('employee_id')) {
            $employeeId = $request->input('employee_id');
        } else {
            // Use the user_id from request if available, otherwise try auth()->id()
            $userId = $request->input('user_id') ?? auth()->id();
            
            // Get employee_id from User model
            $user = User::find($userId);
            $employeeId = $user ? $user->u_employee_id : (string)$userId;
        }
        
        $data = $request->all();
        $data['rd_updated_by'] = $employeeId;
        
        // Remove user_id and employee_id from the data
        if (isset($data['user_id'])) {
            unset($data['user_id']);
        }
        
        if (isset($data['employee_id'])) {
            unset($data['employee_id']);
        }
        
        $recipeDetail->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Recipe detail updated successfully',
            'data' => $recipeDetail
        ]);
    }

    /**
     * Remove the specified recipe detail resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $recipeDetail = RecipeDetail::find($id);
        
        if (!$recipeDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe detail not found'
            ], 404);
        }
        
        $recipeDetail->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Recipe detail deleted successfully'
        ]);
    }
    
    /**
     * Get employee ID from user ID (private helper method).
     *
     * @param int $userId
     * @return string
     */
    private function getEmployeeIdFromUserId($userId)
    {
        // Find the user by ID
        $user = User::find($userId);
        
        if ($user && $user->u_employee_id) {
            return $user->u_employee_id;
        }
        
        // Return user ID as string if employee ID is not available
        return (string) $userId;
    }
}