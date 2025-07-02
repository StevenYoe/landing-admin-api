<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Models\RecipeCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

// Controller for managing Recipe resources via API
class RecipeController extends Controller
{
    /**
     * Display a paginated listing of the recipe resources.
     * Supports filtering by category and active status, sorting, and pagination.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'r_id');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 10);
        $categoryId = $request->input('category_id');
        $isActive = $request->input('is_active');
        
        $query = Recipe::with('categories', 'detail');
        
        // Apply filter by category if provided
        if ($categoryId) {
            $query->whereHas('categories', function($q) use ($categoryId) {
                $q->where('recipe_category.rc_id', $categoryId);
            });
        }
        
        // Apply filter by active status if provided
        if ($isActive !== null) {
            $query->where('r_is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }
        
        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);
        
        // Paginate results
        $recipes = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Recipes retrieved successfully',
            'data' => $recipes
        ]);
    }
    
    /**
     * Get all recipes without pagination, including categories, with optional filtering and sorting.
     * Useful for dropdowns or full lists.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function all(Request $request)
    {
        // Build the query
        $query = Recipe::with('categories');
        
        // Filter by active status if provided
        $isActive = $request->input('is_active');
        if ($isActive !== null) {
            $query->where('r_is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }
        
        // Filter by category if provided
        $categoryId = $request->input('category_id');
        if ($categoryId) {
            $query->whereHas('categories', function($q) use ($categoryId) {
                $q->where('recipe_category.rc_id', $categoryId);
            });
        }
        
        // Apply sorting
        $sortBy = $request->input('sort_by', 'r_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Get the results
        $recipes = $query->get();
        
        return response()->json([
            'success' => true,
            'message' => 'All recipes retrieved successfully',
            'data' => $recipes
        ]);
    }

    /**
     * Store a newly created recipe resource in storage.
     * Handles validation, image upload, category attachment, and sets created_by using employee_id or user_id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'r_title_id' => 'required|string|max:255',
            'r_title_en' => 'required|string|max:255',
            'r_is_active' => 'nullable|boolean',
            'r_image' => 'nullable|image|max:5120',
            'category_ids' => 'required|array',
            'category_ids.*' => 'required|integer|exists:pazar_recipe_category,rc_id',
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
        
        $data = $request->except('category_ids', 'user_id', 'employee_id');
        $data['r_created_by'] = $employeeId;
        
        // Handle image upload
        if ($request->hasFile('r_image')) {
            $file = $request->file('r_image');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('recipes', $filename, 'public');
            $data['r_image'] = $path;
        }
        
        $recipe = Recipe::create($data);
        
        // Attach categories
        $recipe->categories()->attach($request->category_ids);
        
        return response()->json([
            'success' => true,
            'message' => 'Recipe created successfully',
            'data' => $recipe->load('categories')
        ], 201);
    }

    /**
     * Display the specified recipe resource by ID, including categories, detail, createdBy, and updatedBy relationships.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $recipe = Recipe::with(['categories', 'detail', 'createdBy', 'updatedBy'])->find($id);
        
        if (!$recipe) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Recipe retrieved successfully',
            'data' => $recipe
        ]);
    }

    /**
     * Update the specified recipe resource in storage.
     * Handles validation, image replacement, category sync, and sets updated_by using employee_id or user_id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $recipe = Recipe::find($id);
        
        if (!$recipe) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'r_title_id' => 'required|string|max:255',
            'r_title_en' => 'required|string|max:255',
            'r_is_active' => 'nullable|boolean',
            'r_image' => 'nullable|image|max:5120',
            'category_ids' => 'required|array',
            'category_ids.*' => 'required|integer|exists:pazar_recipe_category,rc_id',
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
        
        $data = $request->except('category_ids', 'user_id', 'employee_id');
        $data['r_updated_by'] = $employeeId;
        
        // Store old image path if exists
        $oldImage = $recipe->r_image;
        
        // Handle image upload
        if ($request->hasFile('r_image')) {
            // Remove old image if exists
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            
            $file = $request->file('r_image');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('recipes', $filename, 'public');
            $data['r_image'] = $path;
        }
        
        $recipe->update($data);
        
        // Sync categories
        $recipe->categories()->sync($request->category_ids);
        
        return response()->json([
            'success' => true,
            'message' => 'Recipe updated successfully',
            'data' => $recipe->load('categories')
        ]);
    }

    /**
     * Remove the specified recipe resource from storage.
     * Also deletes the associated image file, detail, and detaches categories if they exist.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $recipe = Recipe::find($id);
        
        if (!$recipe) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found'
            ], 404);
        }
        
        // Remove image if exists
        if ($recipe->r_image) {
            Storage::disk('public')->delete($recipe->r_image);
        }
        
        // Check if recipe has related data that should be removed first
        if ($recipe->detail) {
            $recipe->detail->delete();
        }
        
        // Detach all categories
        $recipe->categories()->detach();
        
        $recipe->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Recipe deleted successfully'
        ]);
    }
    
    /**
     * Get recipes by category.
     * Returns all active recipes for a given category.
     *
     * @param  int  $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByCategory($categoryId)
    {
        $category = RecipeCategory::find($categoryId);
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe category not found'
            ], 404);
        }
        
        $recipes = $category->recipes()
            ->where('r_is_active', true)
            ->with('categories')
            ->get();
            
        return response()->json([
            'success' => true,
            'message' => 'Recipes by category retrieved successfully',
            'data' => $recipes
        ]);
    }
    
    /**
     * Get all recipes for the frontend - Custom endpoint.
     * Useful for populating dropdowns or lists in the frontend.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllRecipes(Request $request)
    {
        // Build the query
        $query = Recipe::with('categories');
        
        // Filter by active status if provided
        $isActive = $request->input('is_active');
        if ($isActive !== null) {
            $query->where('r_is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }
        
        // Filter by category if provided
        $categoryId = $request->input('category_id');
        if ($categoryId) {
            $query->whereHas('categories', function($q) use ($categoryId) {
                $q->where('recipe_category.rc_id', $categoryId);
            });
        }
        
        // Apply sorting
        $sortBy = $request->input('sort_by', 'r_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Get the results
        $recipes = $query->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Recipes retrieved successfully',
            'data' => $recipes
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