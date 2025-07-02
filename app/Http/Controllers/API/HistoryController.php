<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

// Controller for managing History resources via API
class HistoryController extends Controller
{
    /**
     * Display a paginated listing of the history resources.
     * Supports sorting and pagination via query parameters.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'hs_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 10);
        
        $query = History::orderBy($sortBy, $sortOrder);
        
        $histories = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Histories retrieved successfully',
            'data' => $histories
        ]);
    }
    
    /**
     * Get all histories without pagination.
     * Useful for dropdowns or full lists.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function all()
    {
        $histories = History::all();
        
        return response()->json([
            'success' => true,
            'message' => 'All histories retrieved successfully',
            'data' => $histories
        ]);
    }

    /**
     * Store a newly created history resource in storage.
     * Handles validation and image upload.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hs_year' => 'required|string|max:50',
            'hs_description_id' => 'required|string',
            'hs_description_en' => 'required|string',
            'hs_image' => 'nullable|image|max:5120',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = $request->except(['hs_image']);
        
        // Handle image upload
        if ($request->hasFile('hs_image')) {
            $file = $request->file('hs_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('histories', $filename, 'public');
            $data['hs_image'] = $path;
        }
        
        $history = History::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'History created successfully',
            'data' => $history
        ], 201);
    }

    /**
     * Display the specified history resource by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $history = History::find($id);
        
        if (!$history) {
            return response()->json([
                'success' => false,
                'message' => 'History not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'History retrieved successfully',
            'data' => $history
        ]);
    }

    /**
     * Update the specified history resource in storage.
     * Handles validation and image replacement.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $history = History::find($id);
        
        if (!$history) {
            return response()->json([
                'success' => false,
                'message' => 'History not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'hs_year' => 'required|string|max:50',
            'hs_description_id' => 'required|string',
            'hs_description_en' => 'required|string',
            'hs_image' => 'nullable|image|max:5120',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = $request->except(['hs_image', 'keep_current_image']);
        
        // Store old image path if exists
        $oldImage = $history->hs_image;
        
        // Handle image upload
        if ($request->hasFile('hs_image')) {
            // Remove old image if exists
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            
            $file = $request->file('hs_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('histories', $filename, 'public');
            $data['hs_image'] = $path;
        } else if (!$request->hasFile('hs_image') && isset($request->keep_current_image) && $request->keep_current_image) {
            // Don't update the image if we're keeping the current one
        } else if (!$request->hasFile('hs_image')) {
            // If no new file is uploaded and keep_current_image is not set,
            // We'll keep the current image by not including hs_image in the update data
        }
        
        $history->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'History updated successfully',
            'data' => $history
        ]);
    }

    /**
     * Remove the specified history resource from storage.
     * Also deletes the associated image file if it exists.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $history = History::find($id);
        
        if (!$history) {
            return response()->json([
                'success' => false,
                'message' => 'History not found'
            ], 404);
        }
        
        // Remove image if exists
        if ($history->hs_image && Storage::disk('public')->exists($history->hs_image)) {
            Storage::disk('public')->delete($history->hs_image);
        }
        
        $history->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'History deleted successfully'
        ]);
    }
}