<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CareerInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

// Controller for managing CareerInfo resources via API
class CareerInfoController extends Controller
{
    /**
     * Display a paginated listing of the career info resources.
     * Supports sorting and pagination via query parameters.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'ci_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 10);
        
        // Query career info items with sorting and pagination
        $query = CareerInfo::orderBy($sortBy, $sortOrder);
        $careerInfos = $query->paginate($perPage);
        
        // Return paginated data as JSON
        return response()->json([
            'success' => true,
            'message' => 'Career info items retrieved successfully',
            'data' => $careerInfos
        ]);
    }
    
    /**
     * Get all career info items without pagination.
     * Useful for dropdowns or full lists.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function all()
    {
        $careerInfos = CareerInfo::all();
        
        return response()->json([
            'success' => true,
            'message' => 'All career info items retrieved successfully',
            'data' => $careerInfos
        ]);
    }

    /**
     * Store a newly created career info resource in storage.
     * Handles validation and image upload.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'ci_title_id' => 'required|string|max:255',
            'ci_title_en' => 'required|string|max:255',
            'ci_description_id' => 'required|string',
            'ci_description_en' => 'required|string',
            'ci_image' => 'required|image|max:5120',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = $request->all();
        
        // Handle image upload and store the file
        if ($request->hasFile('ci_image')) {
            $file = $request->file('ci_image');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('careers', $filename, 'public');
            $data['ci_image'] = $path;
        }
        
        // Create the career info record
        $careerInfo = CareerInfo::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Career info item created successfully',
            'data' => $careerInfo
        ], 201);
    }

    /**
     * Display the specified career info resource by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $careerInfo = CareerInfo::find($id);
        
        if (!$careerInfo) {
            return response()->json([
                'success' => false,
                'message' => 'Career info item not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Career info item retrieved successfully',
            'data' => $careerInfo
        ]);
    }

    /**
     * Update the specified career info resource in storage.
     * Handles validation and image replacement.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $careerInfo = CareerInfo::find($id);
        
        if (!$careerInfo) {
            return response()->json([
                'success' => false,
                'message' => 'Career info item not found'
            ], 404);
        }
        
        // Validate input data
        $validator = Validator::make($request->all(), [
            'ci_title_id' => 'required|string|max:255',
            'ci_title_en' => 'required|string|max:255',
            'ci_description_id' => 'required|string',
            'ci_description_en' => 'required|string',
            'ci_image' => 'nullable|image|max:5120',
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
        $oldImage = $careerInfo->ci_image;
        
        // Handle image upload and replace old image if needed
        if ($request->hasFile('ci_image')) {
            // Remove old image if exists
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            
            $file = $request->file('ci_image');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('careers', $filename, 'public');
            $data['ci_image'] = $path;
        }
        
        // Update the career info record
        $careerInfo->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Career info item updated successfully',
            'data' => $careerInfo
        ]);
    }

    /**
     * Remove the specified career info resource from storage.
     * Also deletes the associated image file if it exists.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $careerInfo = CareerInfo::find($id);
        
        if (!$careerInfo) {
            return response()->json([
                'success' => false,
                'message' => 'Career info item not found'
            ], 404);
        }
        
        // Remove image file from storage if it exists
        if ($careerInfo->ci_image && Storage::disk('public')->exists($careerInfo->ci_image)) {
            Storage::disk('public')->delete($careerInfo->ci_image);
        }
        
        // Delete the career info record
        $careerInfo->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Career info item deleted successfully'
        ]);
    }
}