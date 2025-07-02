<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Employment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EmploymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'e_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 10);
        $isActive = $request->input('is_active');
        
        $query = Employment::orderBy($sortBy, $sortOrder);
        
        // Apply filter by active status if provided
        if ($isActive !== null) {
            $query->where('e_is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }
        
        $employments = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Employments retrieved successfully',
            'data' => $employments
        ]);
    }
    
    /**
     * Get all employments without pagination
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function all(Request $request)
    {
        // Build the query
        $query = Employment::select('e_id', 'e_title_id', 'e_title_en');
        
        // Filter by active status if provided
        $isActive = $request->input('is_active');
        if ($isActive !== null) {
            $query->where('e_is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }
        
        // Apply sorting
        $sortBy = $request->input('sort_by', 'e_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Get the results
        $employments = $query->get();
        
        return response()->json([
            'success' => true,
            'message' => 'All employments retrieved successfully',
            'data' => $employments
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
            'e_title_id' => 'required|string|max:100',
            'e_title_en' => 'required|string|max:100',
            'e_is_active' => 'nullable|boolean',
            'user_id' => 'nullable|integer',
            'employee_id' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $validated = $validator->validated();
        
        // Check if employee_id is provided directly
        if ($request->has('employee_id')) {
            $employeeId = $request->input('employee_id');
        } else {
            // Use the user_id from request if available, otherwise try auth()->id()
            $userId = $request->input('user_id') ?? auth()->id();
            
            // Get employee_id from User model
            $user = User::find($userId);
            $employeeId = $user ? $user->u_employee_id : (string)$userId;
        }
        
        // Use employee ID for created_by
        $validated['e_created_by'] = $employeeId;
        
        // Remove user_id and employee_id from the validated data as they're not columns in the table
        if (isset($validated['user_id'])) {
            unset($validated['user_id']);
        }
        
        if (isset($validated['employee_id'])) {
            unset($validated['employee_id']);
        }
        
        $employment = Employment::create($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Employment created successfully',
            'data' => $employment
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
        $employment = Employment::with(['createdBy', 'updatedBy'])->find($id);
        
        if (!$employment) {
            return response()->json([
                'success' => false,
                'message' => 'Employment not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Employment retrieved successfully',
            'data' => $employment
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
        $employment = Employment::find($id);
        
        if (!$employment) {
            return response()->json([
                'success' => false,
                'message' => 'Employment not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'e_title_id' => 'required|string|max:100',
            'e_title_en' => 'required|string|max:100',
            'e_is_active' => 'nullable|boolean',
            'user_id' => 'nullable|integer',
            'employee_id' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $validated = $validator->validated();
        
        // Check if employee_id is provided directly
        if ($request->has('employee_id')) {
            $employeeId = $request->input('employee_id');
        } else {
            // Use the user_id from request if available, otherwise try auth()->id()
            $userId = $request->input('user_id') ?? auth()->id();
            
            // Get employee_id from User model
            $user = User::find($userId);
            $employeeId = $user ? $user->u_employee_id : (string)$userId;
        }
        
        // Use employee ID for updated_by
        $validated['e_updated_by'] = $employeeId;
        
        // Remove user_id and employee_id from the validated data
        if (isset($validated['user_id'])) {
            unset($validated['user_id']);
        }
        
        if (isset($validated['employee_id'])) {
            unset($validated['employee_id']);
        }
        
        $employment->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Employment updated successfully',
            'data' => $employment
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
        $employment = Employment::find($id);
        
        if (!$employment) {
            return response()->json([
                'success' => false,
                'message' => 'Employment not found'
            ], 404);
        }
        
        // Check if employment has related vacancies
        if ($employment->vacancies()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete employment with associated vacancies. Remove the vacancies first.'
            ], 400);
        }
        
        $employment->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Employment deleted successfully'
        ]);
    }
    
    /**
     * Get all employments for the frontend - Custom endpoint
     * 
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllEmployments(Request $request)
    {
        // Build the query
        $query = Employment::select('e_id', 'e_title_id', 'e_title_en');
        
        // Filter by active status if provided
        $isActive = $request->input('is_active');
        if ($isActive !== null) {
            $query->where('e_is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }
        
        // Apply sorting
        $sortBy = $request->input('sort_by', 'e_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Get the results
        $employments = $query->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Employments retrieved successfully',
            'data' => $employments
        ]);
    }
}