<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'da_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 10);
        $isActive = $request->input('is_active');
        
        $query = Department::orderBy($sortBy, $sortOrder);
        
        // Apply filter by active status if provided
        if ($isActive !== null) {
            $query->where('da_is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }
        
        $departments = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Departments retrieved successfully',
            'data' => $departments
        ]);
    }
    
    /**
     * Get all departments without pagination
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function all(Request $request)
    {
        // Build the query
        $query = Department::select('da_id', 'da_title_id', 'da_title_en');
        
        // Filter by active status if provided
        $isActive = $request->input('is_active');
        if ($isActive !== null) {
            $query->where('da_is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }
        
        // Apply sorting
        $sortBy = $request->input('sort_by', 'da_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Get the results
        $departments = $query->get();
        
        return response()->json([
            'success' => true,
            'message' => 'All departments retrieved successfully',
            'data' => $departments
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
            'da_title_id' => 'required|string|max:100',
            'da_title_en' => 'required|string|max:100',
            'da_is_active' => 'nullable|boolean',
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
        $validated['da_created_by'] = $employeeId;
        
        // Remove user_id and employee_id from the validated data as they're not columns in the table
        if (isset($validated['user_id'])) {
            unset($validated['user_id']);
        }
        
        if (isset($validated['employee_id'])) {
            unset($validated['employee_id']);
        }
        
        $department = Department::create($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Department created successfully',
            'data' => $department
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
        $department = Department::with(['createdBy', 'updatedBy'])->find($id);
        
        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'Department not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Department retrieved successfully',
            'data' => $department
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
        $department = Department::find($id);
        
        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'Department not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'da_title_id' => 'required|string|max:100',
            'da_title_en' => 'required|string|max:100',
            'da_is_active' => 'nullable|boolean',
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
        $validated['da_updated_by'] = $employeeId;
        
        // Remove user_id and employee_id from the validated data
        if (isset($validated['user_id'])) {
            unset($validated['user_id']);
        }
        
        if (isset($validated['employee_id'])) {
            unset($validated['employee_id']);
        }
        
        $department->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Department updated successfully',
            'data' => $department
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
        $department = Department::find($id);
        
        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'Department not found'
            ], 404);
        }
        
        // Check if department has related vacancies
        if ($department->vacancies()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete department with associated vacancies. Remove the vacancies first.'
            ], 400);
        }
        
        $department->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully'
        ]);
    }
    
    /**
     * Get all departments for the frontend - Custom endpoint
     * 
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllDepartments(Request $request)
    {
        // Build the query
        $query = Department::select('da_id', 'da_title_id', 'da_title_en');
        
        // Filter by active status if provided
        $isActive = $request->input('is_active');
        if ($isActive !== null) {
            $query->where('da_is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }
        
        // Apply sorting
        $sortBy = $request->input('sort_by', 'da_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Get the results
        $departments = $query->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Departments retrieved successfully',
            'data' => $departments
        ]);
    }
}