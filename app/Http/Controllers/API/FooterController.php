<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Footer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FooterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'f_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 10);
        
        $query = Footer::orderBy($sortBy, $sortOrder);
        
        $footers = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Footers retrieved successfully',
            'data' => $footers
        ]);
    }
    
    /**
     * Get all footers without pagination
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function all()
    {
        $footers = Footer::all();
        
        return response()->json([
            'success' => true,
            'message' => 'All footers retrieved successfully',
            'data' => $footers
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
            'f_type' => 'required|string|max:50',
            'f_label_id' => 'required|string|max:255',
            'f_label_en' => 'required|string|max:255',
            'f_description_id' => 'nullable|string',
            'f_description_en' => 'nullable|string',
            'f_icon' => 'nullable|file|mimes:svg,jpg,jpeg,png,gif|max:2048',
            'f_link' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = $request->except(['f_icon']);
        
        // Handle image upload
        if ($request->hasFile('f_icon')) {
            $file = $request->file('f_icon');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('footers', $filename, 'public');
            $data['f_icon'] = $path;
        }
        
        $footer = Footer::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Footer created successfully',
            'data' => $footer
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
        $footer = Footer::find($id);
        
        if (!$footer) {
            return response()->json([
                'success' => false,
                'message' => 'Footer not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Footer retrieved successfully',
            'data' => $footer
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
        $footer = Footer::find($id);
        
        if (!$footer) {
            return response()->json([
                'success' => false,
                'message' => 'Footer not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'f_type' => 'required|string|max:50',
            'f_label_id' => 'required|string|max:255',
            'f_label_en' => 'required|string|max:255',
            'f_description_id' => 'nullable|string',
            'f_description_en' => 'nullable|string',
            'f_icon' => 'nullable|file|mimes:svg,jpg,jpeg,png,gif|max:2048',
            'f_link' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = $request->except(['f_icon', 'keep_current_icon']);
        
        // Store old image path if exists
        $oldImage = $footer->f_icon;
        
        // Handle image upload
        if ($request->hasFile('f_icon')) {
            // Remove old image if exists
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            
            $file = $request->file('f_icon');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('footers', $filename, 'public');
            $data['f_icon'] = $path;
        } else if (!$request->hasFile('f_icon') && isset($request->keep_current_icon) && $request->keep_current_icon) {
            // Don't update the icon if we're keeping the current one
            // This is handled by not including f_icon in the $data array
        } else if (!$request->hasFile('f_icon')) {
            // If no new file is uploaded and keep_current_icon is not set,
            // We'll keep the current icon by not including f_icon in the update data
        }
        
        $footer->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Footer updated successfully',
            'data' => $footer
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
        $footer = Footer::find($id);
        
        if (!$footer) {
            return response()->json([
                'success' => false,
                'message' => 'Footer not found'
            ], 404);
        }
        
        // Remove image if exists
        if ($footer->f_icon && Storage::disk('public')->exists($footer->f_icon)) {
            Storage::disk('public')->delete($footer->f_icon);
        }
        
        $footer->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Footer deleted successfully'
        ]);
    }
}