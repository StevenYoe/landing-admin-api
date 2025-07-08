<?php
// API\DashboardController.php - Modified getStatistics method

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Certification;
use App\Models\History;
use App\Models\WhyPazar;
use App\Models\Recipe;
use App\Models\RecipeCategory;
use App\Models\Vacancy;
use App\Models\Department;
use App\Models\Experience;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Controller for providing dashboard statistics for the API
class DashboardController extends Controller
{
    /**
     * Get dashboard statistics for products, categories, certifications, histories, whyPazars, and recipes.
     * Includes counts, recent items, and grouped statistics for dashboard display.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics(Request $request)
    {
        try {
            // Handle DB queries with proper error handling
            $totalProducts = Product::count();
            $totalCategories = ProductCategory::count();
            $totalCertifications = Certification::count();
            $totalHistories = History::count();
            $totalWhyPazars = WhyPazar::count();
            $activeProducts = Product::where('p_is_active', true)->count();
            
            // Get new products from current month
            $currentMonth = Carbon::now()->startOfMonth();
            $newProductsThisMonth = Product::where('p_created_at', '>=', $currentMonth)->count();
            
            // Get recent products with category info (try-catch for safety)
            try {
                $recentProducts = Product::with(['category'])
                    ->orderBy('p_created_at', 'desc')
                    ->take(10)
                    ->get()
                    ->map(function ($product) {
                        return [
                            'p_id' => $product->p_id,
                            'p_title_en' => $product->p_title_en,
                            'p_title_id' => $product->p_title_id,
                            'p_is_active' => $product->p_is_active,
                            'category_name' => $product->category ? $product->category->pc_title_id : 'N/A'
                        ];
                    });
            } catch (\Exception $e) {
                $recentProducts = [];
            }
            
            // Get products grouped by category (try-catch for safety)
            try {
                $productsByCategory = ProductCategory::select('pc_id', 'pc_title_id')
                    ->withCount(['products as product_count' => function ($query) {
                        $query->where('p_is_active', true);
                    }])
                    ->orderBy('pc_id', 'asc')
                    ->get();
            } catch (\Exception $e) {
                $productsByCategory = [];
            }
            
            try {
                // Count recipes and recipe categories
                $totalRecipes = Recipe::count();
                $totalRecipeCategories = RecipeCategory::count();
                $activeRecipes = Recipe::where('r_is_active', true)->count();
                
                // Get new recipes from current month
                $newRecipesThisMonth = Recipe::where('r_created_at', '>=', $currentMonth)->count();
                
                // Get latest recipes with all categories
                $latestRecipes = Recipe::with(['categories'])
                ->orderBy('r_created_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($recipe) {
                    // Get all categories for each recipe
                    $categoryNames = [];
                    if ($recipe->categories->isNotEmpty()) {
                        foreach ($recipe->categories as $category) {
                            $categoryNames[] = $category->rc_title_id;
                        }
                    }
                    
                    return [
                        'r_id' => $recipe->r_id,
                        'r_title_en' => $recipe->r_title_en,
                        'r_title_id' => $recipe->r_title_id,
                        'r_is_active' => $recipe->r_is_active,
                        'category_names' => $categoryNames, // Array of all categories
                        'category_name' => count($categoryNames) > 0 ? implode(', ', $categoryNames) : 'N/A' // String for backward compatibility
                    ];
                });
                
                // Get recipes grouped by category
                $recipesByCategory = RecipeCategory::select('rc_id', 'rc_title_id')
                    ->withCount(['recipes as recipe_count' => function ($query) {
                        $query->where('r_is_active', true);
                    }])
                    ->orderBy('rc_id', 'asc')
                    ->get();
                
            } catch (\Exception $e) {
                $totalRecipes = 0;
                $totalRecipeCategories = 0;
                $activeRecipes = 0;
                $newRecipesThisMonth = 0;
                $latestRecipes = [];
                $recipesByCategory = [];
            }
            
            // Aggregate all statistics for dashboard
            $statistics = [
                'total_products' => $totalProducts,
                'total_categories' => $totalCategories,
                'total_certifications' => $totalCertifications,
                'total_histories' => $totalHistories,
                'total_whyPazars' => $totalWhyPazars,
                'active_products' => $activeProducts,
                'new_products_this_month' => $newProductsThisMonth,
                'recent_products' => $recentProducts,
                'products_by_category' => $productsByCategory,
                
                // Recipe statistics
                'total_recipes' => $totalRecipes,
                'total_recipe_categories' => $totalRecipeCategories,
                'active_recipes' => $activeRecipes,
                'new_recipes_this_month' => $newRecipesThisMonth,
                'latest_recipes' => $latestRecipes,
                'recipes_by_category' => $recipesByCategory
            ];
            
            // Return the statistics as a JSON response
            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            // Handle any exceptions and return an error response
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get vacancy statistics for HR dashboard.
     * Includes counts, latest vacancies, and grouped statistics for dashboard display.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVacancyStatistics(Request $request)
    {
        try {
            // Count total, active, and urgent vacancies
            $totalVacancies = Vacancy::count();
            $activeVacancies = Vacancy::where('v_is_active', true)->count();
            $urgentVacancies = Vacancy::where('v_urgent', true)
                ->where('v_is_active', true)
                ->count();
            
            // Count departments and experiences
            $totalDepartments = Department::count();
            $totalExperiences = Experience::count();
            
            // Get latest vacancies with relationships (try-catch for safety)
            try {
                $latestVacancies = Vacancy::with(['department', 'experience'])
                    ->orderBy('v_created_at', 'desc')
                    ->take(10)
                    ->get()
                    ->map(function ($vacancy) {
                        return [
                            'v_id' => $vacancy->v_id,
                            'v_title_id' => $vacancy->v_title_id,
                            'v_title_en' => $vacancy->v_title_en,
                            'v_type' => $vacancy->v_type,
                            'v_urgent' => $vacancy->v_urgent,
                            'v_is_active' => $vacancy->v_is_active,
                            'v_posted_date' => $vacancy->v_posted_date,
                            'v_closed_date' => $vacancy->v_closed_date,
                            'department_name' => $vacancy->department ? $vacancy->department->da_title_en : 'N/A',
                            'experience_level' => $vacancy->experience ? $vacancy->experience->ex_title_en : 'N/A'
                        ];
                    });
            } catch (\Exception $e) {
                $latestVacancies = [];
            }
            
            // Get vacancies grouped by department (try-catch for safety)
            try {
                $vacanciesByDepartment = Department::select('da_id', 'da_title_en')
                    ->withCount(['vacancies as vacancy_count' => function ($query) {
                        $query->where('v_is_active', true);
                    }])
                    ->orderBy('da_id', 'asc')
                    ->get();
            } catch (\Exception $e) {
                $vacanciesByDepartment = [];
            }
            
            // Aggregate all statistics for HR dashboard
            $statistics = [
                'total_vacancies' => $totalVacancies,
                'active_vacancies' => $activeVacancies,
                'urgent_vacancies' => $urgentVacancies,
                'total_departments' => $totalDepartments,
                'total_experiences' => $totalExperiences,
                'latest_vacancies' => $latestVacancies,
                'vacancies_by_department' => $vacanciesByDepartment
            ];
            
            // Return the statistics as a JSON response
            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
            
        } catch (\Exception $e) {
            // Handle any exceptions and return an error response
            return response()->json([
                'success' => false,
                'message' => 'Error fetching vacancy statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}