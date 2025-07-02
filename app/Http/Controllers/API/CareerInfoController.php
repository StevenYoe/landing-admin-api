<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CareerInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CareerInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'ci_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 10);
        
        $query = CareerInfo::orderBy($sortBy, $sortOrder);
        
        $careerInfos = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Career info items retrieved successfully',
            'data' => $careerInfos
        ]);
    }
    
    /**
     * Get all career info items without pagination
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
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
        
        // Handle image upload
        if ($request->hasFile('ci_image')) {
            $file = $request->file('ci_image');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('careers', $filename, 'public');
            $data['ci_image'] = $path;
        }
        
        $careerInfo = CareerInfo::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Career info item created successfully',
            'data' => $careerInfo
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
     * Update the specified resource in storage.
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
        
        // Handle image upload
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
        
        $careerInfo->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Career info item updated successfully',
            'data' => $careerInfo
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
        $careerInfo = CareerInfo::find($id);
        
        if (!$careerInfo) {
            return response()->json([
                'success' => false,
                'message' => 'Career info item not found'
            ], 404);
        }
        
        // Remove image if exists
        if ($careerInfo->ci_image && Storage::disk('public')->exists($careerInfo->ci_image)) {
            Storage::disk('public')->delete($careerInfo->ci_image);
        }
        
        $careerInfo->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Career info item deleted successfully'
        ]);
    }
}