<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProductCatalog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

// Controller for managing ProductCatalog resources via API
class ProductCatalogController extends Controller
{
    /**
     * Display a paginated listing of the product catalog resources.
     * Supports sorting and pagination via query parameters.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'pct_id');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 10);
        
        $query = ProductCatalog::with(['createdBy', 'updatedBy']);
        
        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);
        
        // Paginate results
        $catalogs = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Product catalogs retrieved successfully',
            'data' => $catalogs
        ]);
    }
    
    /**
     * Get all product catalogs without pagination.
     * Useful for dropdowns or full lists, with optional sorting.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function all(Request $request)
    {
        // Build the query
        $query = ProductCatalog::query();
        
        // Apply sorting
        $sortBy = $request->input('sort_by', 'pct_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Get the results
        $catalogs = $query->get();
        
        return response()->json([
            'success' => true,
            'message' => 'All product catalogs retrieved successfully',
            'data' => $catalogs
        ]);
    }

    /**
     * Get the latest product catalog file by language (ID or EN).
     * Returns the file URL and name for download.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCatalogByLanguage(Request $request)
    {
        $language = $request->input('lang', 'id'); // default to Indonesian
        
        // Get the latest catalog
        $catalog = ProductCatalog::orderBy('pct_created_at', 'desc')->first();
        
        if (!$catalog) {
            return response()->json([
                'success' => false,
                'message' => 'No catalog found'
            ], 404);
        }
        
        // Return appropriate file based on language
        $fileField = $language === 'en' ? 'pct_catalog_en' : 'pct_catalog_id';
        $fileName = $language === 'en' ? 'Product_Catalog_EN.pdf' : 'Katalog_Produk_ID.pdf';
        
        if (!$catalog->$fileField) {
            return response()->json([
                'success' => false,
                'message' => 'Catalog file not found for the selected language'
            ], 404);
        }
        
        // Fix: Pastikan URL tidak duplikat
        $filePath = $catalog->$fileField;
        
        // Generate clean URL
        $fileUrl = config('app.url') . '/storage/' . $filePath;
        
        return response()->json([
            'success' => true,
            'message' => 'Catalog retrieved successfully',
            'data' => [
                'file_url' => $fileUrl,
                'file_name' => $fileName,
                'language' => $language
            ]
        ]);
    }

    /**
     * Store a newly created product catalog resource in storage.
     * Handles validation, file upload, and sets created_by using employee_id or user_id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Fixed validation rules to accept files instead of strings
        $validator = Validator::make($request->all(), [
            'pct_catalog_id' => 'nullable|file|max:51200', // 50MB limit for Indonesian catalog
            'pct_catalog_en' => 'nullable|file|max:51200', // 50MB limit for English catalog
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
        $validated['pct_created_by'] = $employeeId;
        
        // Handle Indonesian catalog file upload
        if ($request->hasFile('pct_catalog_id')) {
            $file = $request->file('pct_catalog_id');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('catalogs', $filename, 'public');
            $validated['pct_catalog_id'] = $path;
        }
        
        // Handle English catalog file upload
        if ($request->hasFile('pct_catalog_en')) {
            $file = $request->file('pct_catalog_en');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('catalogs', $filename, 'public');
            $validated['pct_catalog_en'] = $path;
        }
        
        // Remove user_id and employee_id from the validated data as they're not columns in the catalog table
        if (isset($validated['user_id'])) {
            unset($validated['user_id']);
        }
        
        if (isset($validated['employee_id'])) {
            unset($validated['employee_id']);
        }
        
        $catalog = ProductCatalog::create($validated);
        
        // Immediately load relationships for the response
        $catalog->load(['createdBy', 'updatedBy']);
        
        return response()->json([
            'success' => true,
            'message' => 'Product catalog created successfully',
            'data' => $catalog
        ], 201);
    }

    /**
     * Display the specified product catalog resource by ID, including createdBy and updatedBy relationships.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $catalog = ProductCatalog::with(['createdBy', 'updatedBy'])->find($id);
        
        if (!$catalog) {
            return response()->json([
                'success' => false,
                'message' => 'Product catalog not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Product catalog retrieved successfully',
            'data' => $catalog
        ]);
    }

    /**
     * Update the specified product catalog resource in storage.
     * Handles validation, file replacement, and sets updated_by using employee_id or user_id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $catalog = ProductCatalog::find($id);
        
        if (!$catalog) {
            return response()->json([
                'success' => false,
                'message' => 'Product catalog not found'
            ], 404);
        }
        
        // Fixed validation rules to accept files instead of strings
        $validator = Validator::make($request->all(), [
            'pct_catalog_id' => 'nullable|file|max:51200', // 50MB limit for Indonesian catalog
            'pct_catalog_en' => 'nullable|file|max:51200', // 50MB limit for English catalog
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
        $validated['pct_updated_by'] = $employeeId;
        
        // Store old file paths if they exist
        $oldIdFile = $catalog->pct_catalog_id ?? null;
        $oldEnFile = $catalog->pct_catalog_en ?? null;
        
        // Handle Indonesian catalog file upload
        if ($request->hasFile('pct_catalog_id')) {
            // Remove old file if exists
            if ($oldIdFile && Storage::disk('public')->exists($oldIdFile)) {
                Storage::disk('public')->delete($oldIdFile);
            }
            
            $file = $request->file('pct_catalog_id');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('catalogs', $filename, 'public');
            $validated['pct_catalog_id'] = $path;
        }
        
        // Handle English catalog file upload
        if ($request->hasFile('pct_catalog_en')) {
            // Remove old file if exists
            if ($oldEnFile && Storage::disk('public')->exists($oldEnFile)) {
                Storage::disk('public')->delete($oldEnFile);
            }
            
            $file = $request->file('pct_catalog_en');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('catalogs', $filename, 'public');
            $validated['pct_catalog_en'] = $path;
        }
        
        // Remove user_id and employee_id from the validated data
        if (isset($validated['user_id'])) {
            unset($validated['user_id']);
        }
        
        if (isset($validated['employee_id'])) {
            unset($validated['employee_id']);
        }
        
        $catalog->update($validated);
        
        // Reload the catalog with its relationships for the response
        $catalog->load(['createdBy', 'updatedBy']);
        
        return response()->json([
            'success' => true,
            'message' => 'Product catalog updated successfully',
            'data' => $catalog
        ]);
    }

    /**
     * Remove the specified product catalog resource from storage.
     * Also deletes the associated files if they exist.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $catalog = ProductCatalog::find($id);
        
        if (!$catalog) {
            return response()->json([
                'success' => false,
                'message' => 'Product catalog not found'
            ], 404);
        }
        
        // Remove Indonesian catalog file if exists
        if (isset($catalog->pct_catalog_id) && $catalog->pct_catalog_id) {
            Storage::disk('public')->delete($catalog->pct_catalog_id);
        }
        
        // Remove English catalog file if exists
        if (isset($catalog->pct_catalog_en) && $catalog->pct_catalog_en) {
            Storage::disk('public')->delete($catalog->pct_catalog_en);
        }
        
        $catalog->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Product catalog deleted successfully'
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
}