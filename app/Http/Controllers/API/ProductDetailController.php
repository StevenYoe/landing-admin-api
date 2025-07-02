<?php

// 1. Updated ProductDetailController.php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProductDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'pd_id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 10);
        
        $query = ProductDetail::with('product')->orderBy($sortBy, $sortOrder);
        
        $productDetails = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Product details retrieved successfully',
            'data' => $productDetails
        ]);
    }

    /**
     * Get product detail by product ID.
     *
     * @param  int  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByProductId($productId)
    {
        $product = Product::find($productId);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
        
        $productDetail = ProductDetail::where('pd_id_product', $productId)->first();
        
        if (!$productDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Product detail not found for this product'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Product detail retrieved successfully',
            'data' => $productDetail
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
            'pd_id_product' => 'required|integer|exists:pazar_product,p_id',
            'pd_net_weight' => 'nullable|string|max:255',
            'pd_longdesc_id' => 'nullable|string',
            'pd_longdesc_en' => 'nullable|string',
            'pd_link_shopee' => 'nullable|string|max:255',
            'pd_link_tokopedia' => 'nullable|string|max:255',
            'pd_link_blibli' => 'nullable|string|max:255',
            'pd_link_lazada' => 'nullable|string|max:255',
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
        
        // Check if product detail already exists for this product
        $existingDetail = ProductDetail::where('pd_id_product', $request->pd_id_product)->first();
        if ($existingDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Product detail already exists for this product',
                'data' => $existingDetail
            ], 422);
        }
        
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
        
        $data = $request->all();
        $data['pd_created_by'] = $employeeId;
        
        // Remove user_id and employee_id from the data as they're not columns in the product_detail table
        if (isset($data['user_id'])) {
            unset($data['user_id']);
        }
        
        if (isset($data['employee_id'])) {
            unset($data['employee_id']);
        }
        
        $productDetail = ProductDetail::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Product detail created successfully',
            'data' => $productDetail
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
        $productDetail = ProductDetail::with('product')->find($id);
        
        if (!$productDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Product detail not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Product detail retrieved successfully',
            'data' => $productDetail
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
        $productDetail = ProductDetail::find($id);
        
        if (!$productDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Product detail not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'pd_longdesc_id' => 'nullable|string',
            'pd_net_weight' => 'nullable|string|max:255',
            'pd_longdesc_en' => 'nullable|string',
            'pd_link_shopee' => 'nullable|string|max:255',
            'pd_link_tokopedia' => 'nullable|string|max:255',
            'pd_link_blibli' => 'nullable|string|max:255',
            'pd_link_lazada' => 'nullable|string|max:255',
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
        
        $data = $request->all();
        $data['pd_updated_by'] = $employeeId;
        
        // Remove user_id and employee_id from the data
        if (isset($data['user_id'])) {
            unset($data['user_id']);
        }
        
        if (isset($data['employee_id'])) {
            unset($data['employee_id']);
        }
        
        $productDetail->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Product detail updated successfully',
            'data' => $productDetail
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
        $productDetail = ProductDetail::find($id);
        
        if (!$productDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Product detail not found'
            ], 404);
        }
        
        $productDetail->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Product detail deleted successfully'
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