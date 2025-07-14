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

// VacancyController handles all API operations related to job vacancies, including CRUD, filtering, and business logic for expiration.
class VacancyController extends Controller
{
    // This controller handles all API operations related to job vacancies, including CRUD, filtering, and special queries.
    
    /**
     * Check and inactive expired vacancies manually.
     * This method finds all active vacancies whose closed date is before today and marks them as inactive.
     * Used for manual triggering of the expiration process.
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
     * Middleware-like method to automatically inactivate expired vacancies before processing requests.
     * Should be called at the start of relevant methods to ensure data consistency.
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
     * Display a paginated list of vacancies, with optional filters and sorting.
     * Filters include department, experience, active status, and urgency.
     * Calls autoCheckExpiredVacancies() to ensure expired jobs are not shown as active.
     */
    public function index(Request $request)
    {
        $this->autoCheckExpiredVacancies();
        
        $sortBy = $request->input('sort_by', 'v_id');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 10);
        $departmentId = $request->input('department_id');
        $experienceId = $request->input('experience_id');
        $isActive = $request->input('is_active');
        $isUrgent = $request->input('is_urgent');
        
        $query = Vacancy::with(['department', 'experience']);
        
        // Apply filters
        if ($departmentId) {
            $query->where('v_department_id', $departmentId);
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
     * Get all vacancies without pagination, with optional filters.
     * Useful for exporting or displaying all data at once.
     */
    public function all(Request $request)
    {
        $this->autoCheckExpiredVacancies();
        
        // Build the query
        $query = Vacancy::with(['department', 'experience']);
        
        // Apply filters
        $departmentId = $request->input('department_id');
        if ($departmentId) {
            $query->where('v_department_id', $departmentId);
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
     * Store a newly created vacancy in the database.
     * Validates input, determines the creator (employee_id), and saves the vacancy.
     * Returns the created vacancy with its relationships.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'v_title_id' => 'required|string|max:255',
            'v_title_en' => 'required|string|max:255',
            'v_department_id' => 'required|integer|exists:career_departments,da_id',
            'v_experience_id' => 'required|integer|exists:career_experiences,ex_id',
            'v_type' => 'nullable|string|in:Onsite,Hybrid,Remote',
            'v_description_id' => 'nullable|string',
            'v_description_en' => 'nullable|string',
            'v_requirement_id' => 'nullable|string',
            'v_requirement_en' => 'nullable|string',
            'v_responsibilities_id' => 'nullable|string',
            'v_responsibilities_en' => 'nullable|string',
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
        $vacancy->load(['department', 'experience']);
        
        return response()->json([
            'success' => true,
            'message' => 'Vacancy created successfully',
            'data' => $vacancy
        ], 201);
    }

    /**
     * Display a single vacancy by its ID, including related department and experience data.
     */
    public function show($id)
    {
        $vacancy = Vacancy::with(['department', 'experience', 'createdBy', 'updatedBy'])->find($id);
        
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
     * Update an existing vacancy by its ID.
     * Validates input, determines the updater (employee_id), and updates the vacancy.
     * Returns the updated vacancy with its relationships.
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
            'v_experience_id' => 'required|integer|exists:career_experiences,ex_id',
            'v_type' => 'nullable|string|in:Onsite,Hybrid,Remote',
            'v_description_id' => 'nullable|string',
            'v_description_en' => 'nullable|string',
            'v_requirement_id' => 'nullable|string',
            'v_requirement_en' => 'nullable|string',
            'v_responsibilities_id' => 'nullable|string',
            'v_responsibilities_en' => 'nullable|string',
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
        $vacancy->load(['department', 'experience']);
        
        return response()->json([
            'success' => true,
            'message' => 'Vacancy updated successfully',
            'data' => $vacancy
        ]);
    }

    /**
     * Delete a vacancy by its ID.
     * Returns a success message if deletion is successful.
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
     * Get all active vacancies for the frontend, filtered by department, experience, and urgency.
     * Only vacancies that are active and within the posting/closing date range are returned.
     */
    public function getActiveVacancies(Request $request)
    {
        $this->autoCheckExpiredVacancies();
        
        // Build the query for active vacancies
        $query = Vacancy::with(['department', 'experience'])
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
        
        $experienceId = $request->input('experience_id');
        if ($experienceId) {
            $query->where('v_experience_id', $experienceId);
        }
        
        $isUrgent = $request->input('is_urgent');
        if ($isUrgent !== null && $isUrgent !== '') {
            $query->where('v_urgent', filter_var($isUrgent, FILTER_VALIDATE_BOOLEAN));
        }
        
        // Order by urgent first, then by closed date (near deadline first)
        $query->orderBy('v_urgent', 'desc')
              ->orderBy('v_closed_date', 'asc');
        
        // Get the results
        $vacancies = $query->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Active vacancies retrieved successfully',
            'data' => $vacancies
        ]);
    }
    
    /**
     * Get details of a vacancy by its ID or slug (URL-friendly title).
     * Supports both numeric IDs and string slugs for flexible frontend routing.
     */
    public function getVacancyDetail($identifier)
    {
        // Determine if the identifier is an ID (numeric) or slug (string)
        if (is_numeric($identifier)) {
            // Search by ID
            $vacancy = Vacancy::with(['department', 'experience'])
                    ->find($identifier);
        } else {
            // Search by slug (matching against title)
            $vacancy = Vacancy::with(['department', 'experience'])
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
     * Get related vacancies by department, excluding the current vacancy.
     * Returns a random selection of related jobs, useful for recommendations.
     */
    public function getRelatedVacancies(Request $request)
    {
        $departmentId = $request->input('department_id');
        $currentVacancyId = $request->input('current_vacancy_id');
        $limit = $request->input('limit', 2);
        
        if (!$departmentId) {
            return response()->json([
                'success' => false,
                'message' => 'Department ID is required'
            ], 422);
        }
        
        $query = Vacancy::with(['department', 'experience'])
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
     * Helper method to get employee ID from a user ID.
     * Returns the employee ID if available, otherwise returns the user ID as a string.
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