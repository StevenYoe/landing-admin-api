<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\HealthController;
use App\Http\Controllers\API\HeaderController;
use App\Http\Controllers\API\FooterController;
use App\Http\Controllers\API\PopupController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProductCatalogController;
use App\Http\Controllers\API\ProductCategoryController;
use App\Http\Controllers\API\ProductDetailController;
use App\Http\Controllers\API\CertificationController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RecipeCategoryController;
use App\Http\Controllers\API\RecipeController;
use App\Http\Controllers\API\RecipeDetailController;
use App\Http\Controllers\API\IndexController;
use App\Http\Controllers\API\TestimonialController;
use App\Http\Controllers\API\WhyPazarController;
use App\Http\Controllers\API\CompanyProfileController;
use App\Http\Controllers\API\HistoryController;
use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\BrandController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\EmploymentController;
use App\Http\Controllers\API\ExperienceController;
use App\Http\Controllers\API\VacancyController;
use App\Http\Controllers\API\CareerInfoController;
use App\Http\Controllers\API\WorkAtPazarController;
use App\Http\Controllers\API\CareerController;

// Health check endpoint - public
Route::get('/health', [HealthController::class, 'check']);

// Token validation endpoint - public tanpa middleware
Route::get('/validate-token', [AuthController::class, 'validateToken']);

// Semua endpoint yang memerlukan autentikasi
Route::middleware(['api'])->group(function () {
    // Dashboard - tidak menggunakan auth:sanctum untuk sementara
    Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics']);
    Route::get('/vacancies/statistics', [DashboardController::class, 'getVacancyStatistics']);
    
    // Index Page Data
    Route::get('/index/data', [IndexController::class, 'getIndexData']);
    
    // Brand Page Data
    Route::get('/brand/data', [BrandController::class, 'getBrandData']);
    
    // Company Page Data
    Route::get('/company/data', [CompanyController::class, 'getCompanyData']);
    
    // Headers
    Route::apiResource('headers', HeaderController::class);
    
    // Footers
    Route::get('/footers', [FooterController::class, 'all']);
    Route::apiResource('footers', FooterController::class);
    
    // Popups
    Route::apiResource('popups', PopupController::class);
    
    Route::post('/popups/deactivate-others', [PopupController::class, 'deactivateOthers']);
    
    // Product Categories
    Route::get('/productcategories/all', [ProductCategoryController::class, 'all']);
    Route::apiResource('productcategories', ProductCategoryController::class);
    
    // Products
    Route::get('/products/getAllProducts', [ProductController::class, 'all']);
    Route::apiResource('products', ProductController::class);

    // Product Catalogs
    Route::get('/productcatalogs/all', [ProductCatalogController::class, 'all']);
    Route::get('/productcatalogs/by-language', [ProductCatalogController::class, 'getCatalogByLanguage']);
    Route::apiResource('productcatalogs', ProductCatalogController::class);
    
    // Product Details
    Route::get('/productdetails/by-product/{productId}', [ProductDetailController::class, 'getByProductId']);
    Route::apiResource('productdetails', ProductDetailController::class);
    
    // Recipe Categories
    Route::get('/recipecategories/all', [RecipeCategoryController::class, 'all']);
    Route::apiResource('recipecategories', RecipeCategoryController::class);
    
    // Recipes
    Route::get('/recipes/getAllRecipes', [RecipeController::class, 'all']); // Changed route name
    Route::apiResource('recipes', RecipeController::class);
    Route::get('/recipes/by-category/{categoryId}', [RecipeController::class, 'getByCategory']);
    
    // Recipe Details
    Route::get('/recipedetails/by-recipe/{recipeId}', [RecipeDetailController::class, 'getByRecipeId']);
    Route::apiResource('recipedetails', RecipeDetailController::class);
    
    // Certifications
    Route::apiResource('certifications', CertificationController::class);
    
    // Company Profiles
    Route::get('/companyprofiles/all', [CompanyProfileController::class, 'all']);
    Route::apiResource('companyprofiles', CompanyProfileController::class);
    Route::get('/companyprofiles/type/{type}', [CompanyProfileController::class, 'getByType']);
    
    // Histories
    Route::get('/histories/all', [HistoryController::class, 'all']);
    Route::apiResource('histories', HistoryController::class);
    
    // Testimonials
    Route::get('/testimonials/all', [TestimonialController::class, 'all']);
    Route::apiResource('testimonials', TestimonialController::class);
    Route::get('/testimonials/type/{type}', [TestimonialController::class, 'getByType']);
    
    // Why Pazar
    Route::get('/whypazars/all', [WhyPazarController::class, 'all']);
    Route::apiResource('whypazars', WhyPazarController::class);
    
    // Career Module Routes
    
    // Departments
    Route::get('/departments/all', [DepartmentController::class, 'all']);
    Route::apiResource('departments', DepartmentController::class);
    
    // Employments
    Route::get('/employments/all', [EmploymentController::class, 'all']);
    Route::apiResource('employments', EmploymentController::class);
    
    // Experiences
    Route::get('/experiences/all', [ExperienceController::class, 'all']);
    Route::apiResource('experiences', ExperienceController::class);
    
    // Vacancies
    Route::get('/vacancies/all', [VacancyController::class, 'all']);
    
    // Fix: Define the active vacancies route BEFORE the resource route
    Route::get('/vacancies/active', [VacancyController::class, 'getActiveVacancies']);
    
    Route::apiResource('vacancies', VacancyController::class);
    
    // In your routes/api.php file
    Route::get('vacancies/getVacancyDetail/{identifier}', [VacancyController::class, 'getVacancyDetail']);
    Route::get('vacancies/getRelatedVacancies', [VacancyController::class, 'getRelatedVacancies']);
    Route::get('/vacancies/check-expired', [VacancyController::class, 'checkExpiredVacancies']);
    
    // Work At Pazars
    Route::get('/workatpazars/all', [WorkAtPazarController::class, 'all']);
    Route::apiResource('workatpazars', WorkAtPazarController::class);
    Route::get('/workatpazars/type/{type}', [WorkAtPazarController::class, 'getByType']);
    
    // Career Infos
    Route::get('/careerinfos/all', [CareerInfoController::class, 'all']);
    Route::apiResource('careerinfos', CareerInfoController::class);
    
    // Career Page Data
    Route::get('/career/data', [CareerController::class, 'getIndexData']);
});