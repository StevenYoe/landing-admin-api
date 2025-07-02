<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Vacancy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class VacancyController extends Controller
{
    // Simpan semua method yang ada sebelumnya...
    
    /**
     * Check and inactive expired vacancies manually.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkExpiredVacancies()
    {
        $today = Carbon::now()->startOfDay();
        
        // Get active vacancies with closed date before today
        $expiredVacancies = Vacancy::where('v_is_active', true)
            ->whereNotNull('v_closed_date')
            ->where('v_closed_date', '<', $today)
            ->get();
        
        $count = 0;
        
        foreach ($expiredVacancies as $vacancy) {
            // Update the vacancy to inactive and set updated_by to "System"
            $vacancy->update([
                'v_is_active' => false,
                'v_updated_by' => 'System'
            ]);
            
            $count++;
        }
        
        return response()->json([
            'success' => true,
            'message' => "Successfully inactivated {$count} expired vacancies."
        ]);
    }
    
    /**
     * A middleware check that can be called at the beginning of relevant methods
     * to automatically check for expired vacancies before processing the request.
     *
     * @return void
     */
    private function autoCheckExpiredVacancies()
    {
        $today = Carbon::now()->startOfDay();
        
        // Get active vacancies with closed date before today
        Vacancy::where('v_is_active', true)
            ->whereNotNull('v_closed_date')
            ->where('v_closed_date', '<', $today)
            ->update([
                'v_is_active' => false,
                'v_updated_by' => 'System',
                'v_updated_at' => now() // Explicitly set updated_at
            ]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->autoCheckExpiredVacancies();
        
        $sortBy = $request->input('sort_by', 'v_id');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 10);
        $departmentId = $request->input('department_id');
        $employmentId = $request->input('employment_id');
        $experienceId = $request->input('experience_id');
        $isActive = $request->input('is_active');
        $isUrgent = $request->input('is_urgent');
        
        $query = Vacancy::with(['department', 'employment', 'experience']);
        
        // Apply filters
        if ($departmentId) {
            $query->where('v_department_id', $departmentId);
        }
        
        if ($employmentId) {
            $query->where('v_employment_id', $employmentId);
        }
        
        if ($experienceId) {
            $query->where('v_experience_id', $experienceId);
        }
        
        if ($isActive !== null && $isActive !== '') {
            $query->where('v_is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }
        
        if ($isUrgent !== null && $isUrgent !== '') {
            $query->where('v_urgent', filter_var($isUrgent, FILTER_VALIDATE_BOOLEAN));
        }
        
        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);
        
        // Paginate results
        $vacancies = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Vacancies retrieved successfully',
            'data' => $vacancies
        ]);
    }
    
    /**
     * Get all vacancies without pagination
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function all(Request $request)
    {
        $this->autoCheckExpiredVacancies();
        
        // Build the query
        $query = Vacancy::with(['department', 'employment', 'experience']);
        
        // Apply filters
        $departmentId = $request->input('department_id');
        if ($departmentId) {
            $query->where('v_department_id', $departmentId);
        }
        
        $employmentId = $request->input('employment_id');
        if ($employmentId) {
            $query->where('v_employment_id', $employmentId);
        }
        
        $experienceId = $request->input('experience_id');
        if ($experienceId) {
            $query->where('v_experience_id', $experienceId);
        }
        
        $isActive = $request->input('is_active');
        if ($isActive !== null && $isActive !== '') {
            $query->where('v_is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }
        
        $isUrgent = $request->input('is_urgent');
        if ($isUrgent !== null && $isUrgent !== '') {
            $query->where('v_urgent', filter_var($isUrgent, FILTER_VALIDATE_BOOLEAN));
        }
        
        // Apply sorting
        $sortBy = $request->input('sort_by', 'v_id');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Get the results
        $vacancies = $query->get();
        
        return response()->json([
            'success' => true,
            'message' => 'All vacancies retrieved successfully',
            'data' => $vacancies
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
            'v_title_id' => 'required|string|max:255',
            'v_title_en' => 'required|string|max:255',
            'v_department_id' => 'required|integer|exists:career_departments,da_id',
            'v_employment_id' => 'required|integer|exists:career_employments,e_id',
            'v_experience_id' => 'required|integer|exists:career_experiences,ex_id',
            'v_type' => 'nullable|string|max:50',
            'v_description_id' => 'required|string',
            'v_description_en' => 'required|string',
            'v_requirement_id' => 'required|string',
            'v_requirement_en' => 'required|string',
            'v_responsibilities_id' => 'required|string',
            'v_responsibilities_en' => 'required|string',
            'v_posted_date' => 'required|date',
            'v_closed_date' => 'nullable|date|after_or_equal:v_posted_date',
            'v_urgent' => 'nullable|boolean',
            'v_is_active' => 'nullable|boolean',
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
        $validated['v_created_by'] = $employeeId;
        
        // Remove user_id and employee_id from the validated data
        if (isset($validated['user_id'])) {
            unset($validated['user_id']);
        }
        
        if (isset($validated['employee_id'])) {
            unset($validated['employee_id']);
        }
        
        $vacancy = Vacancy::create($validated);
        
        // Load relationships for the response
        $vacancy->load(['department', 'employment', 'experience']);
        
        return response()->json([
            'success' => true,
            'message' => 'Vacancy created successfully',
            'data' => $vacancy
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
        $vacancy = Vacancy::with(['department', 'employment', 'experience', 'createdBy', 'updatedBy'])->find($id);
        
        if (!$vacancy) {
            return response()->json([
                'success' => false,
                'message' => 'Vacancy not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Vacancy retrieved successfully',
            'data' => $vacancy
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
        $vacancy = Vacancy::find($id);
        
        if (!$vacancy) {
            return response()->json([
                'success' => false,
                'message' => 'Vacancy not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'v_title_id' => 'required|string|max:255',
            'v_title_en' => 'required|string|max:255',
            'v_department_id' => 'required|integer|exists:career_departments,da_id',
            'v_employment_id' => 'required|integer|exists:career_employments,e_id',
            'v_experience_id' => 'required|integer|exists:career_experiences,ex_id',
            'v_type' => 'nullable|string|max:50',
            'v_description_id' => 'required|string',
            'v_description_en' => 'required|string',
            'v_requirement_id' => 'required|string',
            'v_requirement_en' => 'required|string',
            'v_responsibilities_id' => 'required|string',
            'v_responsibilities_en' => 'required|string',
            'v_posted_date' => 'required|date',
            'v_closed_date' => 'nullable|date|after_or_equal:v_posted_date',
            'v_urgent' => 'nullable|boolean',
            'v_is_active' => 'nullable|boolean',
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
        $validated['v_updated_by'] = $employeeId;
        
        // Remove user_id and employee_id from the validated data
        if (isset($validated['user_id'])) {
            unset($validated['user_id']);
        }
        
        if (isset($validated['employee_id'])) {
            unset($validated['employee_id']);
        }
        
        $vacancy->update($validated);
        
        // Load relationships for the response
        $vacancy->load(['department', 'employment', 'experience']);
        
        return response()->json([
            'success' => true,
            'message' => 'Vacancy updated successfully',
            'data' => $vacancy
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
        $vacancy = Vacancy::find($id);
        
        if (!$vacancy) {
            return response()->json([
                'success' => false,
                'message' => 'Vacancy not found'
            ], 404);
        }
        
        $vacancy->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Vacancy deleted successfully'
        ]);
    }
    
    /**
     * Get all active vacancies for the frontend
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveVacancies(Request $request)
    {
        $this->autoCheckExpiredVacancies();
        
        // Build the query for active vacancies
        $query = Vacancy::with(['department', 'employment', 'experience'])
                ->where('v_is_active', true)
                ->where('v_posted_date', '<=', now()->toDateString())
                ->where(function($q) {
                    $q->whereNull('v_closed_date')
                      ->orWhere('v_closed_date', '>=', now()->toDateString());
                });
        
        // Apply filters
        $departmentId = $request->input('department_id');
        if ($departmentId) {
            $query->where('v_department_id', $departmentId);
        }
        
        $employmentId = $request->input('employment_id');
        if ($employmentId) {
            $query->where('v_employment_id', $employmentId);
        }
        
        $experienceId = $request->input('experience_id');
        if ($experienceId) {
            $query->where('v_experience_id', $experienceId);
        }
        
        $isUrgent = $request->input('is_urgent');
        if ($isUrgent !== null && $isUrgent !== '') {
            $query->where('v_urgent', filter_var($isUrgent, FILTER_VALIDATE_BOOLEAN));
        }
        
        // Order by urgent first, then by posted date (newest first)
        $query->orderBy('v_urgent', 'desc')
              ->orderBy('v_posted_date', 'desc');
        
        // Get the results
        $vacancies = $query->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Active vacancies retrieved successfully',
            'data' => $vacancies
        ]);
    }
    
    /**
     * Get vacancy details by ID or slug
     *
     * @param string|int $identifier Vacancy ID or slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVacancyDetail($identifier)
    {
        // Determine if the identifier is an ID (numeric) or slug (string)
        if (is_numeric($identifier)) {
            // Search by ID
            $vacancy = Vacancy::with(['department', 'employment', 'experience'])
                    ->find($identifier);
        } else {
            // Search by slug (matching against title)
            $vacancy = Vacancy::with(['department', 'employment', 'experience'])
                    ->where(function($query) use ($identifier) {
                        $query->whereRaw("LOWER(TRIM(REPLACE(v_title_id, ' ', '-'))) = ?", [strtolower($identifier)])
                            ->orWhereRaw("LOWER(TRIM(REPLACE(v_title_en, ' ', '-'))) = ?", [strtolower($identifier)]);
                    })
                    ->first();
        }
        
        if (!$vacancy) {
            return response()->json([
                'success' => false,
                'message' => 'Vacancy not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Vacancy details retrieved successfully',
            'data' => $vacancy
        ]);
    }
    
    /**
     * Get related vacancies by department ID
     *
     * @param int $departmentId Department ID
     * @param int $currentVacancyId Current vacancy ID to exclude
     * @param int $limit Maximum number of related vacancies to return
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRelatedVacancies(Request $request)
    {
        $departmentId = $request->input('department_id');
        $currentVacancyId = $request->input('current_vacancy_id');
        $limit = $request->input('limit', 3);
        
        if (!$departmentId) {
            return response()->json([
                'success' => false,
                'message' => 'Department ID is required'
            ], 422);
        }
        
        $query = Vacancy::with(['department', 'employment', 'experience'])
                ->where('v_department_id', $departmentId)
                ->where('v_is_active', true)
                ->where('v_posted_date', '<=', now()->toDateString())
                ->where(function($q) {
                    $q->whereNull('v_closed_date')
                      ->orWhere('v_closed_date', '>=', now()->toDateString());
                });
        
        // Exclude current vacancy if provided
        if ($currentVacancyId) {
            $query->where('v_id', '!=', $currentVacancyId);
        }
        
        // Get random vacancies
        $vacancies = $query->inRandomOrder()
                    ->limit($limit)
                    ->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Related vacancies retrieved successfully',
            'data' => $vacancies
        ]);
    }
    
    /**
     * Get employee ID from user ID
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
}