<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'pc_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 10);
        
        $query = ProductCategory::orderBy($sortBy, $sortOrder);
        
        $categories = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Product categories retrieved successfully',
            'data' => $categories
        ]);
    }
    
    /**
     * Get all product categories without pagination
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function all()
    {
        // Ensure we select the necessary fields in a consistent format
        $categories = ProductCategory::select('pc_id', 'pc_title_id', 'pc_title_en')->get();
        
        return response()->json([
            'success' => true,
            'message' => 'All product categories retrieved successfully',
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
            'pc_title_id' => 'required|string|max:255',
            'pc_title_en' => 'required|string|max:255',
            'pc_description_id' => 'nullable|string',
            'pc_description_en' => 'nullable|string',
            'pc_image' => 'nullable|image|max:5120',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = $request->all();
        
        // Handle image upload
        if ($request->hasFile('pc_image')) {
            $file = $request->file('pc_image');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('product_categories', $filename, 'public');
            $data['pc_image'] = $path;
        }
        
        $category = ProductCategory::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Product category created successfully',
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
        $category = ProductCategory::find($id);
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Product category not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Product category retrieved successfully',
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
        $category = ProductCategory::find($id);
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Product category not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'pc_title_id' => 'required|string|max:255',
            'pc_title_en' => 'required|string|max:255',
            'pc_description_id' => 'nullable|string',
            'pc_description_en' => 'nullable|string',
            'pc_image' => 'nullable|image|max:5120',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = $request->all();
        
        // Store old image path if exists
        $oldImage = $category->pc_image;
        
        // Handle image upload
        if ($request->hasFile('pc_image')) {
            // Remove old image if exists
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            
            $file = $request->file('pc_image');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('product_categories', $filename, 'public');
            $data['pc_image'] = $path;
        }
        
        $category->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Product category updated successfully',
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
        $category = ProductCategory::find($id);
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Product category not found'
            ], 404);
        }
        
        // Check if category has related products
        if ($category->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with associated products. Remove the products first.'
            ], 400);
        }
        
        // Remove image if exists
        if ($category->pc_image) {
            Storage::disk('public')->delete($category->pc_image);
        }
        
        $category->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Product category deleted successfully'
        ]);
    }
}