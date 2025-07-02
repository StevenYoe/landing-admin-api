<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

// Controller for managing CompanyProfile resources via API
class CompanyProfileController extends Controller
{
    /**
     * Display a paginated listing of the company profile resources.
     * Supports sorting and pagination via query parameters.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'cp_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 10);
        
        $query = CompanyProfile::orderBy($sortBy, $sortOrder);
        
        $companyProfiles = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Company profiles retrieved successfully',
            'data' => $companyProfiles
        ]);
    }
    
    /**
     * Get all company profiles without pagination.
     * Useful for dropdowns or full lists.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function all()
    {
        $companyProfiles = CompanyProfile::all();
        
        return response()->json([
            'success' => true,
            'message' => 'All company profiles retrieved successfully',
            'data' => $companyProfiles
        ]);
    }

    /**
     * Store a newly created company profile resource in storage.
     * Handles validation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cp_description_id' => 'required|string',
            'cp_description_en' => 'required|string',
            'cp_type' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $companyProfile = CompanyProfile::create($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Company profile created successfully',
            'data' => $companyProfile
        ], 201);
    }

    /**
     * Display the specified company profile resource by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $companyProfile = CompanyProfile::find($id);
        
        if (!$companyProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Company profile not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Company profile retrieved successfully',
            'data' => $companyProfile
        ]);
    }

    /**
     * Update the specified company profile resource in storage.
     * Handles validation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $companyProfile = CompanyProfile::find($id);
        
        if (!$companyProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Company profile not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'cp_description_id' => 'required|string',
            'cp_description_en' => 'required|string',
            'cp_type' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $companyProfile->update($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Company profile updated successfully',
            'data' => $companyProfile
        ]);
    }

    /**
     * Remove the specified company profile resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $companyProfile = CompanyProfile::find($id);
        
        if (!$companyProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Company profile not found'
            ], 404);
        }
        
        $companyProfile->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Company profile deleted successfully'
        ]);
    }
    
    /**
     * Get company profile by type (e.g., what, policy, vision, mission)
     *
     * @param  string  $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByType($type)
    {
        $companyProfile = CompanyProfile::where('cp_type', $type)->first();
        
        if (!$companyProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Company profile not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Company profile retrieved successfully',
            'data' => $companyProfile
        ]);
    }
}