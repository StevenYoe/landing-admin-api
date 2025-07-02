<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Header;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

// Controller for managing Header resources via API
class HeaderController extends Controller
{
    /**
     * Display a paginated listing of the header resources.
     * Supports sorting and pagination via query parameters.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'h_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 10);
        
        $query = Header::orderBy($sortBy, $sortOrder);
        
        $headers = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Headers retrieved successfully',
            'data' => $headers
        ]);
    }
    
    /**
     * Get all headers without pagination.
     * Useful for dropdowns or full lists.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function all()
    {
        $headers = Header::all();
        
        return response()->json([
            'success' => true,
            'message' => 'All headers retrieved successfully',
            'data' => $headers
        ]);
    }

    /**
     * Store a newly created header resource in storage.
     * Handles validation and image upload.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'h_title_id' => 'required|string|max:255',
            'h_title_en' => 'required|string|max:255',
            'h_page_name' => 'required|string|max:50',
            'h_description_id' => 'nullable|string',
            'h_description_en' => 'nullable|string',
            'h_image' => 'nullable|image|max:5120',
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
        if ($request->hasFile('h_image')) {
            $file = $request->file('h_image');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('headers', $filename, 'public');
            $data['h_image'] = $path;
        }
        
        $header = Header::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Header created successfully',
            'data' => $header
        ], 201);
    }

    /**
     * Display the specified header resource by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $header = Header::find($id);
        
        if (!$header) {
            return response()->json([
                'success' => false,
                'message' => 'Header not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Header retrieved successfully',
            'data' => $header
        ]);
    }

    /**
     * Update the specified header resource in storage.
     * Handles validation and image replacement.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $header = Header::find($id);
        
        if (!$header) {
            return response()->json([
                'success' => false,
                'message' => 'Header not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'h_title_id' => 'required|string|max:255',
            'h_title_en' => 'required|string|max:255',
            'h_page_name' => 'required|string|max:50',
            'h_description_id' => 'nullable|string',
            'h_description_en' => 'nullable|string',
            'h_image' => 'nullable|image|max:5120',
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
        $oldImage = $header->h_image;
        
        // Handle image upload
        if ($request->hasFile('h_image')) {
            // Remove old image if exists
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            
            $file = $request->file('h_image');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('headers', $filename, 'public');
            $data['h_image'] = $path;
        }
        
        $header->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Header updated successfully',
            'data' => $header
        ]);
    }

    /**
     * Remove the specified header resource from storage.
     * Also deletes the associated image file if it exists.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $header = Header::find($id);
        
        if (!$header) {
            return response()->json([
                'success' => false,
                'message' => 'Header not found'
            ], 404);
        }
        
        // Remove image if exists
        if ($header->h_image) {
            Storage::disk('public')->delete($header->h_image);
        }
        
        $header->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Header deleted successfully'
        ]);
    }
}