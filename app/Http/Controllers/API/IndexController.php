<?php
// API\IndexController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Header;
use App\Models\Popup;
use App\Models\ProductCategory;
use App\Models\Recipe;
use App\Models\RecipeCategory;
use App\Models\WhyPazar;
use Carbon\Carbon;

class IndexController extends Controller
{
    /**
     * Get all data needed for the index page
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getIndexData(Request $request)
    {
        try {
            // Get active popup
            $popup = Popup::where('pu_is_active', true)->first();
            
            // Get header specifically for the index page
            // Extract page_name from request or default to 'index'
            $pageName = $request->input('page_name', 'index');
            $header = Header::where('h_page_name', $pageName)->first();
            
            // Fallback to first header if none found with the specific page name
            if (!$header) {
                $header = Header::first();
            }
            
            // Get product categories
            $productCategories = ProductCategory::get();
            
            // Get all "Why Pazar" items
            $whyPazarItems = WhyPazar::all();
            
            // Get latest recipe
            $latestRecipe = Recipe::with(['categories'])
                ->where('r_is_active', true)
                ->orderBy('r_created_at', 'desc')
                ->first();
            
            // Format recipe with category names if available
            $formattedLatestRecipe = null;
            if ($latestRecipe) {
                $categoryNames = [];
                if ($latestRecipe->categories && $latestRecipe->categories->isNotEmpty()) {
                    foreach ($latestRecipe->categories as $category) {
                        $categoryNames[] = $category->rc_title_id;
                    }
                }
                
                $formattedLatestRecipe = [
                    'r_id' => $latestRecipe->r_id,
                    'r_title_en' => $latestRecipe->r_title_en,
                    'r_title_id' => $latestRecipe->r_title_id,
                    'r_image' => $latestRecipe->r_image,
                    'r_is_active' => $latestRecipe->r_is_active,
                    'category_names' => $categoryNames,
                    'category_name' => count($categoryNames) > 0 ? implode(', ', $categoryNames) : 'N/A'
                ];
            }
            
            $indexData = [
                'popup' => $popup,
                'header' => $header,
                'product_categories' => $productCategories,
                'why_pazar_items' => $whyPazarItems,
                'latest_recipe' => $formattedLatestRecipe
            ];
            
            return response()->json([
                'success' => true,
                'data' => $indexData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching index data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}