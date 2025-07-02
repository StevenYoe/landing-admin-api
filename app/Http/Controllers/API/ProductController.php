<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

// Controller for managing Product resources via API
class ProductController extends Controller
{
    /**
     * Display a paginated listing of the product resources.
     * Supports filtering by category and active status, sorting, and pagination.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'p_id');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 10);
        $categoryId = $request->input('category_id');
        $isActive = $request->input('is_active');
        
        $query = Product::with('category', 'detail');
        
        // Apply filter by category if provided
        if ($categoryId) {
            $query->where('p_id_product_category', $categoryId);
        }
        
        // Apply filter by active status if provided
        if ($isActive !== null) {
            $query->where('p_is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }
        
        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);
        
        // Paginate results
        $products = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products
        ]);
    }
    
    /**
     * Get all products without pagination, including category, with optional filtering and sorting.
     * Useful for dropdowns or full lists.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function all(Request $request)
    {
        // Build the query
        $query = Product::with('category');
        
        // Filter by active status if provided
        $isActive = $request->input('is_active');
        if ($isActive !== null) {
            $query->where('p_is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }
        
        // Filter by category if provided
        $categoryId = $request->input('category_id');
        if ($categoryId) {
            $query->where('p_id_product_category', $categoryId);
        }
        
        // Apply sorting
        $sortBy = $request->input('sort_by', 'p_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Get the results
        $products = $query->get();
        
        return response()->json([
            'success' => true,
            'message' => 'All products retrieved successfully',
            'data' => $products
        ]);
    }

    /**
     * Store a newly created product resource in storage.
     * Handles validation, image upload, and sets created_by using employee_id or user_id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'p_id_product_category' => 'required|integer|exists:pazar_product_category,pc_id',
            'p_title_id' => 'required|string|max:255',
            'p_title_en' => 'required|string|max:255',
            'p_description_id' => 'nullable|string',
            'p_description_en' => 'nullable|string',
            'p_is_active' => 'nullable|boolean',
            'p_image' => 'nullable|image|max:5120',
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
        $validated['p_created_by'] = $employeeId;
        
        // Handle image upload
        if ($request->hasFile('p_image')) {
            $file = $request->file('p_image');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('products', $filename, 'public');
            $validated['p_image'] = $path;
        }
        
        // Remove user_id and employee_id from the validated data as they're not columns in the product table
        if (isset($validated['user_id'])) {
            unset($validated['user_id']);
        }
        
        if (isset($validated['employee_id'])) {
            unset($validated['employee_id']);
        }
        
        $product = Product::create($validated);
        
        // Immediately load the category relationship for the response
        $product->load('category');
        
        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    /**
     * Display the specified product resource by ID, including category, detail, createdBy, and updatedBy relationships.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $product = Product::with(['category', 'detail', 'createdBy', 'updatedBy'])->find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully',
            'data' => $product
        ]);
    }

    /**
     * Update the specified product resource in storage.
     * Handles validation, image replacement, and sets updated_by using employee_id or user_id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'p_id_product_category' => 'required|integer|exists:pazar_product_category,pc_id',
            'p_title_id' => 'required|string|max:255',
            'p_title_en' => 'required|string|max:255',
            'p_description_id' => 'nullable|string',
            'p_description_en' => 'nullable|string',
            'p_is_active' => 'nullable|boolean',
            'p_image' => 'nullable|image|max:5120',
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
        $validated['p_updated_by'] = $employeeId;
        
        // Store old image path if exists
        $oldImage = $product->p_image;
        
        // Handle image upload
        if ($request->hasFile('p_image')) {
            // Remove old image if exists
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            
            $file = $request->file('p_image');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('products', $filename, 'public');
            $validated['p_image'] = $path;
        }
        
        // Remove user_id and employee_id from the validated data
        if (isset($validated['user_id'])) {
            unset($validated['user_id']);
        }
        
        if (isset($validated['employee_id'])) {
            unset($validated['employee_id']);
        }
        
        $product->update($validated);
        
        // Reload the product with its category relationship for the response
        $product->load('category');
        
        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    /**
     * Remove the specified product resource from storage.
     * Also deletes the associated image file and related detail if it exists.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
        
        // Remove image if exists
        if ($product->p_image) {
            Storage::disk('public')->delete($product->p_image);
        }
        
        // Check if product has related data that should be removed first
        if ($product->detail()->count() > 0) {
            $product->detail()->delete();
        }
        
        $product->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
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
     * Get all products for the frontend - Custom endpoint.
     * Useful for populating dropdowns or lists in the frontend.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllProducts(Request $request)
    {
        // Build the query
        $query = Product::with('category');
        
        // Filter by active status if provided
        $isActive = $request->input('is_active');
        if ($isActive !== null) {
            $query->where('p_is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }
        
        // Filter by category if provided
        $categoryId = $request->input('category_id');
        if ($categoryId) {
            $query->where('p_id_product_category', $categoryId);
        }
        
        // Apply sorting
        $sortBy = $request->input('sort_by', 'p_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Get the results
        $products = $query->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products
        ]);
    }
}