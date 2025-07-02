<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

// Controller for managing Testimonial resources via API
class TestimonialController extends Controller
{
    /**
     * Display a paginated listing of the testimonial resources.
     * Supports sorting and pagination via query parameters.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 't_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 10);
        
        $query = Testimonial::orderBy($sortBy, $sortOrder);
        
        $testimonials = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Testimonials retrieved successfully',
            'data' => $testimonials
        ]);
    }
    
    /**
     * Get all testimonials without pagination.
     * Useful for dropdowns or full lists.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function all()
    {
        $testimonials = Testimonial::all();
        
        return response()->json([
            'success' => true,
            'message' => 'All testimonials retrieved successfully',
            'data' => $testimonials
        ]);
    }

    /**
     * Store a newly created testimonial resource in storage.
     * Handles validation and image upload.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            't_name' => 'required|string|max:255',
            't_description_id' => 'required|string',
            't_description_en' => 'required|string',
            't_type' => 'required|string|max:50',
            't_gender' => 'required|in:Male,Female',
            't_image' => 'nullable|image|max:5120',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = $request->except(['t_image']);
        
        // Handle image upload
        if ($request->hasFile('t_image')) {
            $file = $request->file('t_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('testimonials', $filename, 'public');
            $data['t_image'] = $path;
        }
        
        $testimonial = Testimonial::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Testimonial created successfully',
            'data' => $testimonial
        ], 201);
    }

    /**
     * Display the specified testimonial resource by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $testimonial = Testimonial::find($id);
        
        if (!$testimonial) {
            return response()->json([
                'success' => false,
                'message' => 'Testimonial not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Testimonial retrieved successfully',
            'data' => $testimonial
        ]);
    }

    /**
     * Update the specified testimonial resource in storage.
     * Handles validation and image replacement.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $testimonial = Testimonial::find($id);
        
        if (!$testimonial) {
            return response()->json([
                'success' => false,
                'message' => 'Testimonial not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            't_name' => 'required|string|max:255',
            't_description_id' => 'required|string',
            't_description_en' => 'required|string',
            't_type' => 'required|string|max:50',
            't_gender' => 'required|in:Male,Female',
            't_image' => 'nullable|image|max:5120',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = $request->except(['t_image', 'keep_current_image']);
        
        // Store old image paths if they exist
        $oldImage = $testimonial->t_image;
        
        // Handle image upload
        if ($request->hasFile('t_image')) {
            // Remove old image if exists
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            
            $file = $request->file('t_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('testimonials', $filename, 'public');
            $data['t_image'] = $path;
        } else if (!$request->hasFile('t_image') && isset($request->keep_current_image) && $request->keep_current_image) {
            // Don't update the image if we're keeping the current one
        } else if (!$request->hasFile('t_image')) {
            // If no new file is uploaded and keep_current_image is not set,
            // We'll keep the current image by not including t_image in the update data
        }
        
        $testimonial->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Testimonial updated successfully',
            'data' => $testimonial
        ]);
    }

    /**
     * Remove the specified testimonial resource from storage.
     * Also deletes the associated image file if it exists.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $testimonial = Testimonial::find($id);
        
        if (!$testimonial) {
            return response()->json([
                'success' => false,
                'message' => 'Testimonial not found'
            ], 404);
        }
        
        // Remove image if exists
        if ($testimonial->t_image && Storage::disk('public')->exists($testimonial->t_image)) {
            Storage::disk('public')->delete($testimonial->t_image);
        }
        
        $testimonial->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Testimonial deleted successfully'
        ]);
    }
    
    /**
     * Get testimonials by type (e.g., customer, chef).
     * Returns all testimonials for a given type.
     *
     * @param  string  $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByType($type)
    {
        $testimonials = Testimonial::where('t_type', $type)->get();
        
        if ($testimonials->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No testimonials found for this type'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Testimonials retrieved successfully',
            'data' => $testimonials
        ]);
    }
}