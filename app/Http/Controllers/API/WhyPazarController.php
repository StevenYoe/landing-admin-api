<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WhyPazar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

// WhyPazarController manages CRUD operations for the WhyPazar resource, including image upload and deletion logic.
class WhyPazarController extends Controller
{
    /**
     * Display a paginated list of WhyPazar items.
     * Supports sorting and pagination via request parameters.
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'w_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 10);
        
        $query = WhyPazar::orderBy($sortBy, $sortOrder);
        
        $whyPazars = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Why Pazar items retrieved successfully',
            'data' => $whyPazars
        ]);
    }
    
    /**
     * Get all WhyPazar items without pagination.
     * Useful for exporting or displaying all data at once.
     */
    public function all()
    {
        $whyPazars = WhyPazar::all();
        
        return response()->json([
            'success' => true,
            'message' => 'All Why Pazar items retrieved successfully',
            'data' => $whyPazars
        ]);
    }

    /**
     * Store a newly created WhyPazar item in the database.
     * Handles validation and optional image upload.
     * Returns the created item.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'w_title_id' => 'required|string|max:255',
            'w_title_en' => 'required|string|max:255',
            'w_description_id' => 'nullable|string',
            'w_description_en' => 'nullable|string',
            'w_image' => 'nullable|image|max:5120',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = $request->except(['w_image']);
        
        // Handle image upload
        if ($request->hasFile('w_image')) {
            $file = $request->file('w_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('why-pazar', $filename, 'public');
            $data['w_image'] = $path;
        }
        
        $whyPazar = WhyPazar::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Why Pazar item created successfully',
            'data' => $whyPazar
        ], 201);
    }

    /**
     * Display a single WhyPazar item by its ID.
     * Returns 404 if the item does not exist.
     */
    public function show($id)
    {
        $whyPazar = WhyPazar::find($id);
        
        if (!$whyPazar) {
            return response()->json([
                'success' => false,
                'message' => 'Why Pazar item not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Why Pazar item retrieved successfully',
            'data' => $whyPazar
        ]);
    }

    /**
     * Update an existing WhyPazar item by its ID.
     * Handles validation, optional image replacement, and image deletion if a new image is uploaded.
     * If 'keep_current_image' is set, the image is not changed.
     */
    public function update(Request $request, $id)
    {
        $whyPazar = WhyPazar::find($id);
        
        if (!$whyPazar) {
            return response()->json([
                'success' => false,
                'message' => 'Why Pazar item not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'w_title_id' => 'required|string|max:255',
            'w_title_en' => 'required|string|max:255',
            'w_description_id' => 'nullable|string',
            'w_description_en' => 'nullable|string',
            'w_image' => 'nullable|image|max:5120',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = $request->except(['w_image', 'keep_current_image']);
        
        // Store old image path if exists
        $oldImage = $whyPazar->w_image;
        
        // Handle image upload
        if ($request->hasFile('w_image')) {
            // Remove old image if exists
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            
            $file = $request->file('w_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('why-pazar', $filename, 'public');
            $data['w_image'] = $path;
        } else if (!$request->hasFile('w_image') && isset($request->keep_current_image) && $request->keep_current_image) {
            // Don't update the image if we're keeping the current one
        } else if (!$request->hasFile('w_image')) {
            // If no new file is uploaded and keep_current_image is not set,
            // We'll keep the current image by not including w_image in the update data
        }
        
        $whyPazar->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Why Pazar item updated successfully',
            'data' => $whyPazar
        ]);
    }

    /**
     * Delete a WhyPazar item by its ID.
     * Also deletes the associated image from storage if it exists.
     */
    public function destroy($id)
    {
        $whyPazar = WhyPazar::find($id);
        
        if (!$whyPazar) {
            return response()->json([
                'success' => false,
                'message' => 'Why Pazar item not found'
            ], 404);
        }
        
        // Remove image if exists
        if ($whyPazar->w_image && Storage::disk('public')->exists($whyPazar->w_image)) {
            Storage::disk('public')->delete($whyPazar->w_image);
        }
        
        $whyPazar->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Why Pazar item deleted successfully'
        ]);
    }
}