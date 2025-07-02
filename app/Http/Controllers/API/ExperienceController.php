<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

// Controller for managing Experience resources via API
class ExperienceController extends Controller
{
    /**
     * Display a paginated listing of the experience resources.
     * Supports searching, sorting, and pagination.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'ex_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        
        $query = Experience::query();
        
        // Add search functionality
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('ex_title_id', 'like', "%{$search}%")
                  ->orWhere('ex_title_en', 'like', "%{$search}%");
            });
        }
        
        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);
        
        // Paginate results
        $experiences = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Experiences retrieved successfully',
            'data' => $experiences
        ]);
    }
    
    /**
     * Get all experiences without pagination.
     * Useful for dropdowns or full lists, with optional searching and sorting.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function all(Request $request)
    {
        // Build the query
        $query = Experience::select('ex_id', 'ex_title_id', 'ex_title_en');
        
        // Apply search if provided
        $search = $request->input('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('ex_title_id', 'like', "%{$search}%")
                  ->orWhere('ex_title_en', 'like', "%{$search}%");
            });
        }
        
        // Apply sorting
        $sortBy = $request->input('sort_by', 'ex_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Get the results
        $experiences = $query->get();
        
        return response()->json([
            'success' => true,
            'message' => 'All experiences retrieved successfully',
            'data' => $experiences
        ]);
    }

    /**
     * Store a newly created experience resource in storage.
     * Handles validation, sets created_by using employee_id or user_id, and removes unnecessary fields.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ex_title_id' => 'required|string|max:100',
            'ex_title_en' => 'required|string|max:100',
            'user_id' => 'nullable|integer',
            'employee_id' => 'nullable|string|max:20',
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
        $validated['ex_created_by'] = $employeeId;
        
        // Remove user_id and employee_id from the validated data
        if (isset($validated['user_id'])) {
            unset($validated['user_id']);
        }
        
        if (isset($validated['employee_id'])) {
            unset($validated['employee_id']);
        }
        
        $experience = Experience::create($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Experience created successfully',
            'data' => $experience
        ], 201);
    }

    /**
     * Display the specified experience resource by ID, including createdBy and updatedBy relationships.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $experience = Experience::with(['createdBy', 'updatedBy'])->find($id);
        
        if (!$experience) {
            return response()->json([
                'success' => false,
                'message' => 'Experience not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Experience retrieved successfully',
            'data' => $experience
        ]);
    }

    /**
     * Update the specified experience resource in storage.
     * Handles validation, sets updated_by using employee_id or user_id, and removes unnecessary fields.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $experience = Experience::find($id);
        
        if (!$experience) {
            return response()->json([
                'success' => false,
                'message' => 'Experience not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'ex_title_id' => 'required|string|max:100',
            'ex_title_en' => 'required|string|max:100',
            'user_id' => 'nullable|integer',
            'employee_id' => 'nullable|string|max:20',
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
        $validated['ex_updated_by'] = $employeeId;
        
        // Remove user_id and employee_id from the validated data
        if (isset($validated['user_id'])) {
            unset($validated['user_id']);
        }
        
        if (isset($validated['employee_id'])) {
            unset($validated['employee_id']);
        }
        
        $experience->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Experience updated successfully',
            'data' => $experience
        ]);
    }

    /**
     * Remove the specified experience resource from storage.
     * Prevents deletion if experience has related vacancies.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $experience = Experience::find($id);
        
        if (!$experience) {
            return response()->json([
                'success' => false,
                'message' => 'Experience not found'
            ], 404);
        }
        
        // Check if experience has related vacancies
        if ($experience->vacancies()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete experience with associated vacancies. Remove the vacancies first.'
            ], 400);
        }
        
        $experience->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Experience deleted successfully'
        ]);
    }
    
    /**
     * Get employee ID from user ID (private helper method).
     *
     * @param int $userId
     * @return string
     */
    private function getEmployeeIdFromUserId($userId)
    {
        // Find the user by ID
        $user = User::find($userId);
        
        if ($user && $user->u_employee_id) {
            return $user->u_employee_id;
        }
        
        // Return user ID as string if employee ID is not available
        return (string) $userId;
    }
    
    /**
     * Get all experiences for the frontend - Custom endpoint.
     * Useful for populating dropdowns or lists in the frontend.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllExperiences(Request $request)
    {
        // Build the query
        $query = Experience::select('ex_id', 'ex_title_id', 'ex_title_en');
        
        // Apply search if provided
        $search = $request->input('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('ex_title_id', 'like', "%{$search}%")
                  ->orWhere('ex_title_en', 'like', "%{$search}%");
            });
        }
        
        // Apply sorting
        $sortBy = $request->input('sort_by', 'ex_title_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Get the results
        $experiences = $query->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Experiences retrieved successfully',
            'data' => $experiences
        ]);
    }
}