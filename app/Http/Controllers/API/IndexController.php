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

// Controller for providing all data needed for the index (landing) page
class IndexController extends Controller
{
    /**
     * Get all data needed for the index page, including popup, header, product categories, why pazar items, and the latest recipe.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getIndexData(Request $request)
    {
        try {
            // Get active popup for the index page
            $popup = Popup::where('pu_is_active', true)->first();
            
            // Get header for the index page, using page_name from request or default to 'index'
            $pageName = $request->input('page_name', 'index');
            $header = Header::where('h_page_name', $pageName)->first();
            
            // Fallback to first header if none found with the specific page name
            if (!$header) {
                $header = Header::first();
            }
            
            // Get all product categories
            $productCategories = ProductCategory::get();
            
            // Get all 'Why Pazar' items
            $whyPazarItems = WhyPazar::all();
            
            // Get the latest active recipe with its categories
            $latestRecipe = Recipe::with(['categories'])
                ->where('r_is_active', true)
                ->orderBy('r_created_at', 'desc')
                ->first();
            
            // Format the latest recipe with category names if available
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
            
            // Aggregate all index page data into a single array
            $indexData = [
                'popup' => $popup,
                'header' => $header,
                'product_categories' => $productCategories,
                'why_pazar_items' => $whyPazarItems,
                'latest_recipe' => $formattedLatestRecipe
            ];
            
            // Return the index data as a JSON response
            return response()->json([
                'success' => true,
                'data' => $indexData
            ]);
        } catch (\Exception $e) {
            // Handle any exceptions and return an error response
            return response()->json([
                'success' => false,
                'message' => 'Error fetching index data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}