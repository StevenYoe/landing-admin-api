<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RecipeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RecipeCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'rc_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 10);
        
        $query = RecipeCategory::orderBy($sortBy, $sortOrder);
        
        $categories = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Recipe categories retrieved successfully',
            'data' => $categories
        ]);
    }
    
    /**
     * Get all recipe categories without pagination
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function all()
    {
        // Ensure we select the necessary fields in a consistent format
        $categories = RecipeCategory::select('rc_id', 'rc_title_id', 'rc_title_en')->get();
        
        return response()->json([
            'success' => true,
            'message' => 'All recipe categories retrieved successfully',
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rc_title_id' => 'required|string|max:255',
            'rc_title_en' => 'required|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $category = RecipeCategory::create($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Recipe category created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $category = RecipeCategory::find($id);
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe category not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Recipe category retrieved successfully',
            'data' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $category = RecipeCategory::find($id);
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe category not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'rc_title_id' => 'required|string|max:255',
            'rc_title_en' => 'required|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $category->update($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Recipe category updated successfully',
            'data' => $category
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $category = RecipeCategory::find($id);
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe category not found'
            ], 404);
        }
        
        // Check if category has related recipes in the junction table
        if ($category->recipes()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with associated recipes. Remove the recipe associations first.'
            ], 400);
        }
        
        $category->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Recipe category deleted successfully'
        ]);
    }
}