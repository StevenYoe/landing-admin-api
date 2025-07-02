<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Certification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CertificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'c_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 10);
        
        $query = Certification::orderBy($sortBy, $sortOrder);
        
        $certifications = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Certifications retrieved successfully',
            'data' => $certifications
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
            'c_label_id' => 'required|string|max:255',
            'c_label_en' => 'required|string|max:255',
            'c_title_id' => 'required|string|max:255',
            'c_title_en' => 'required|string|max:255',
            'c_description_id' => 'nullable|string',
            'c_description_en' => 'nullable|string',
            'c_image' => 'nullable|image|max:5120',
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
        if ($request->hasFile('c_image')) {
            $file = $request->file('c_image');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('certifications', $filename, 'public');
            $data['c_image'] = $path;
        }
        
        // Remove c_created_by as it's not in the model's fillable array
        // and it's not clear if the database supports this field
        
        $certification = Certification::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Certification created successfully',
            'data' => $certification
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
        $certification = Certification::find($id);
        
        if (!$certification) {
            return response()->json([
                'success' => false,
                'message' => 'Certification not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Certification retrieved successfully',
            'data' => $certification
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
        $certification = Certification::find($id);
        
        if (!$certification) {
            return response()->json([
                'success' => false,
                'message' => 'Certification not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'c_label_id' => 'required|string|max:255',
            'c_label_en' => 'required|string|max:255',
            'c_title_id' => 'required|string|max:255',
            'c_title_en' => 'required|string|max:255',
            'c_description_id' => 'nullable|string',
            'c_description_en' => 'nullable|string',
            'c_image' => 'nullable|image|max:5120',
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
        $oldImage = $certification->c_image;
        
        // Handle image upload
        if ($request->hasFile('c_image')) {
            // Remove old image if exists
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            
            $file = $request->file('c_image');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('certifications', $filename, 'public');
            $data['c_image'] = $path;
        }
        
        $certification->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Certification updated successfully',
            'data' => $certification
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
        $certification = Certification::find($id);
        
        if (!$certification) {
            return response()->json([
                'success' => false,
                'message' => 'Certification not found'
            ], 404);
        }
        
        // Remove image if exists
        if ($certification->c_image) {
            Storage::disk('public')->delete($certification->c_image);
        }
        
        $certification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Certification deleted successfully'
        ]);
    }
}