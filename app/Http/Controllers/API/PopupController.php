<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Popup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

// Controller for managing Popup resources via API
class PopupController extends Controller
{
    /**
     * Display a paginated listing of the popup resources.
     * Supports sorting and pagination via query parameters.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'pu_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 10);
        
        $query = Popup::orderBy($sortBy, $sortOrder);
        
        $popups = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Popups retrieved successfully',
            'data' => $popups
        ]);
    }
    
    /**
     * Get all popups without pagination.
     * Useful for dropdowns or full lists.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function all()
    {
        $popups = Popup::all();
        
        return response()->json([
            'success' => true,
            'message' => 'All popups retrieved successfully',
            'data' => $popups
        ]);
    }

    /**
     * Store a newly created popup resource in storage.
     * Handles validation and image upload. New popups are set to inactive by default.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pu_link' => 'nullable|string|max:255',
            'pu_is_active' => 'nullable|boolean',
            'pu_image' => 'required|image|max:5120',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = $request->all();
        
        // Explicitly set new popups to inactive
        $data['pu_is_active'] = false;
        
        // Handle image upload
        if ($request->hasFile('pu_image')) {
            $file = $request->file('pu_image');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('popups', $filename, 'public');
            $data['pu_image'] = $path;
        }
        
        $popup = Popup::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Popup created successfully',
            'data' => $popup
        ], 201);
    }

    /**
     * Display the specified popup resource by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $popup = Popup::find($id);
        
        if (!$popup) {
            return response()->json([
                'success' => false,
                'message' => 'Popup not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Popup retrieved successfully',
            'data' => $popup
        ]);
    }

    /**
     * Update the specified popup resource in storage.
     * Handles validation, image replacement, and ensures only one popup is active at a time.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $popup = Popup::find($id);
        
        if (!$popup) {
            return response()->json([
                'success' => false,
                'message' => 'Popup not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'pu_link' => 'nullable|string|max:255',
            'pu_is_active' => 'nullable|boolean',
            'pu_image' => 'nullable|image|max:5120',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = $request->all();
        
        // Properly handle the boolean value
        $data['pu_is_active'] = $request->input('pu_is_active') == '1' ? true : false;
        
        // If setting to active, deactivate all others
        if ($data['pu_is_active']) {
            Popup::where('pu_id', '!=', $id)->update(['pu_is_active' => false]);
        }
        
        // Store old image path if exists
        $oldImage = $popup->pu_image;
        
        // Handle image upload
        if ($request->hasFile('pu_image')) {
            // Remove old image if exists
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            
            $file = $request->file('pu_image');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('popups', $filename, 'public');
            $data['pu_image'] = $path;
        }
        
        $popup->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Popup updated successfully',
            'data' => $popup
        ]);
    }

    /**
     * Remove the specified popup resource from storage.
     * Also deletes the associated image file if it exists.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $popup = Popup::find($id);
        
        if (!$popup) {
            return response()->json([
                'success' => false,
                'message' => 'Popup not found'
            ], 404);
        }
        
        // Remove image if exists
        if ($popup->pu_image) {
            Storage::disk('public')->delete($popup->pu_image);
        }
        
        $popup->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Popup deleted successfully'
        ]);
    }
    
    /**
     * Deactivate all popups except the specified one.
     * Useful for ensuring only one popup is active at a time.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deactivateOthers(Request $request)
    {
        $excludeId = $request->input('exclude_id');
        
        // Update all popups except the excluded one
        Popup::where('pu_id', '!=', $excludeId)
            ->update(['pu_is_active' => false]);
        
        return response()->json([
            'success' => true,
            'message' => 'All other popups have been deactivated'
        ]);
    }
}