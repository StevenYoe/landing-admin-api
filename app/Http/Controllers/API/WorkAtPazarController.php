<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WorkAtPazar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

// WorkAtPazarController manages CRUD operations for the WorkAtPazar resource, including filtering by type.
class WorkAtPazarController extends Controller
{
    /**
     * Display a paginated list of WorkAtPazar items.
     * Supports sorting and pagination via request parameters.
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'wap_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 10);
        
        $query = WorkAtPazar::orderBy($sortBy, $sortOrder);
        
        $workAtPazars = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Work at Pazar items retrieved successfully',
            'data' => $workAtPazars
        ]);
    }
    
    /**
     * Get all WorkAtPazar items without pagination.
     * Useful for exporting or displaying all data at once.
     */
    public function all()
    {
        $workAtPazars = WorkAtPazar::all();
        
        return response()->json([
            'success' => true,
            'message' => 'All work at Pazar items retrieved successfully',
            'data' => $workAtPazars
        ]);
    }

    /**
     * Store a newly created WorkAtPazar item in the database.
     * Handles validation and saves the item.
     * Returns the created item.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wap_title_id' => 'required|string|max:255',
            'wap_title_en' => 'required|string|max:255',
            'wap_description_id' => 'nullable|string',
            'wap_description_en' => 'nullable|string',
            'wap_type' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $workAtPazar = WorkAtPazar::create($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Work at Pazar item created successfully',
            'data' => $workAtPazar
        ], 201);
    }

    /**
     * Display a single WorkAtPazar item by its ID.
     * Returns 404 if the item does not exist.
     */
    public function show($id)
    {
        $workAtPazar = WorkAtPazar::find($id);
        
        if (!$workAtPazar) {
            return response()->json([
                'success' => false,
                'message' => 'Work at Pazar item not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Work at Pazar item retrieved successfully',
            'data' => $workAtPazar
        ]);
    }

    /**
     * Update an existing WorkAtPazar item by its ID.
     * Handles validation and updates the item.
     * Returns the updated item.
     */
    public function update(Request $request, $id)
    {
        $workAtPazar = WorkAtPazar::find($id);
        
        if (!$workAtPazar) {
            return response()->json([
                'success' => false,
                'message' => 'Work at Pazar item not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'wap_title_id' => 'required|string|max:255',
            'wap_title_en' => 'required|string|max:255',
            'wap_description_id' => 'nullable|string',
            'wap_description_en' => 'nullable|string',
            'wap_type' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $workAtPazar->update($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Work at Pazar item updated successfully',
            'data' => $workAtPazar
        ]);
    }

    /**
     * Delete a WorkAtPazar item by its ID.
     * Returns a success message if deletion is successful.
     */
    public function destroy($id)
    {
        $workAtPazar = WorkAtPazar::find($id);
        
        if (!$workAtPazar) {
            return response()->json([
                'success' => false,
                'message' => 'Work at Pazar item not found'
            ], 404);
        }
        
        $workAtPazar->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Work at Pazar item deleted successfully'
        ]);
    }
    
    /**
     * Get WorkAtPazar items filtered by type.
     * Useful for displaying items of a specific category or section.
     */
    public function getByType($type)
    {
        $workAtPazars = WorkAtPazar::where('wap_type', $type)->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Work at Pazar items retrieved successfully',
            'data' => $workAtPazars
        ]);
    }
}