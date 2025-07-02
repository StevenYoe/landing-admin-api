<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProductCatalog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ProductCatalogController extends Controller
{
    /**
     * Display a listing of the resource.
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
     * Get all product catalogs without pagination
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
     * Get catalog by language
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
        
        // Bersihkan path dari duplikasi storage URL
        $filePath = str_replace('http://127.0.0.1:8002/storage/', '', $filePath);
        $filePath = ltrim($filePath, '/');
        
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pct_catalog_id' => 'required|string|max:255',
            'pct_catalog_en' => 'required|string|max:255',
            'pct_file' => 'nullable|file|max:51200', // 50MB limit
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
        
        // Handle file upload
        if ($request->hasFile('pct_file')) {
            $file = $request->file('pct_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('catalogs', $filename, 'public');
            $validated['pct_file'] = $path;
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
     * Display the specified resource.
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
     * Update the specified resource in storage.
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
        
        $validator = Validator::make($request->all(), [
            'pct_catalog_id' => 'required|string|max:255',
            'pct_catalog_en' => 'required|string|max:255',
            'pct_file' => 'nullable|file|max:51200', // 50MB limit
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
        
        // Store old file path if exists
        $oldFile = $catalog->pct_file ?? null;
        
        // Handle file upload
        if ($request->hasFile('pct_file')) {
            // Remove old file if exists
            if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                Storage::disk('public')->delete($oldFile);
            }
            
            $file = $request->file('pct_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('catalogs', $filename, 'public');
            $validated['pct_file'] = $path;
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
     * Remove the specified resource from storage.
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
        
        // Remove file if exists
        if (isset($catalog->pct_file) && $catalog->pct_file) {
            Storage::disk('public')->delete($catalog->pct_file);
        }
        
        $catalog->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Product catalog deleted successfully'
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