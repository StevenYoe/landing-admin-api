<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WorkAtPazar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkAtPazarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
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
     * Get all work at pazar items without pagination
     *
     * @return \Illuminate\Http\JsonResponse
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
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
     * Get work at pazar items by type
     *
     * @param  string  $type
     * @return \Illuminate\Http\JsonResponse
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