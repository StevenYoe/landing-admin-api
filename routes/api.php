<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// Import all API controllers for route definitions
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
use App\Http\Controllers\API\ExperienceController;
use App\Http\Controllers\API\VacancyController;
use App\Http\Controllers\API\CareerInfoController;
use App\Http\Controllers\API\WorkAtPazarController;
use App\Http\Controllers\API\CareerController;

// -----------------------------
// API ROUTES CONFIGURATION FILE
// -----------------------------
// This file defines all API endpoints for the application.
// It organizes routes for public and authenticated access, and groups related endpoints by feature/module.
//
// - Public endpoints: Health check, token validation
// - Authenticated endpoints: All business logic, CRUD, and data retrieval for the admin panel and frontend
// - Uses route groups and middleware for access control
// - Follows RESTful conventions with apiResource where possible

// Health check endpoint - public
Route::get('/health', [HealthController::class, 'check']);

// Token validation endpoint - public, no middleware
Route::get('/validate-token', [AuthController::class, 'validateToken']);

// All endpoints that require authentication
Route::middleware(['api'])->group(function () {
    // Dashboard statistics endpoints
    Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics']);
    Route::get('/vacancies/statistics', [DashboardController::class, 'getVacancyStatistics']);
    
    // Index, brand, and company data endpoints
    Route::get('/index/data', [IndexController::class, 'getIndexData']);
    Route::get('/brand/data', [BrandController::class, 'getBrandData']);
    Route::get('/company/data', [CompanyController::class, 'getCompanyData']);
    
    // Header and footer endpoints
    Route::apiResource('headers', HeaderController::class);
    Route::get('/footers', [FooterController::class, 'all']);
    Route::apiResource('footers', FooterController::class);
    
    // Popup endpoints
    Route::apiResource('popups', PopupController::class);
    Route::post('/popups/deactivate-others', [PopupController::class, 'deactivateOthers']);
    
    // Product category, product, and catalog endpoints
    Route::get('/productcategories/all', [ProductCategoryController::class, 'all']);
    Route::apiResource('productcategories', ProductCategoryController::class);
    Route::get('/products/getAllProducts', [ProductController::class, 'all']);
    Route::apiResource('products', ProductController::class);
    Route::get('/productcatalogs/all', [ProductCatalogController::class, 'all']);
    Route::get('/productcatalogs/by-language', [ProductCatalogController::class, 'getCatalogByLanguage']);
    Route::apiResource('productcatalogs', ProductCatalogController::class);
    Route::get('/productdetails/by-product/{productId}', [ProductDetailController::class, 'getByProductId']);
    Route::apiResource('productdetails', ProductDetailController::class);
    
    // Recipe category, recipe, and detail endpoints
    Route::get('/recipecategories/all', [RecipeCategoryController::class, 'all']);
    Route::apiResource('recipecategories', RecipeCategoryController::class);
    Route::get('/recipes/getAllRecipes', [RecipeController::class, 'all']); // All recipes
    Route::apiResource('recipes', RecipeController::class);
    Route::get('/recipes/by-category/{categoryId}', [RecipeController::class, 'getByCategory']);
    Route::get('/recipedetails/by-recipe/{recipeId}', [RecipeDetailController::class, 'getByRecipeId']);
    Route::apiResource('recipedetails', RecipeDetailController::class);
    
    // Certification endpoints
    Route::apiResource('certifications', CertificationController::class);
    
    // Company profile endpoints
    Route::get('/companyprofiles/all', [CompanyProfileController::class, 'all']);
    Route::apiResource('companyprofiles', CompanyProfileController::class);
    Route::get('/companyprofiles/type/{type}', [CompanyProfileController::class, 'getByType']);
    
    // History endpoints
    Route::get('/histories/all', [HistoryController::class, 'all']);
    Route::apiResource('histories', HistoryController::class);
    
    // Testimonial endpoints
    Route::get('/testimonials/all', [TestimonialController::class, 'all']);
    Route::apiResource('testimonials', TestimonialController::class);
    Route::get('/testimonials/type/{type}', [TestimonialController::class, 'getByType']);
    
    // Why Pazar endpoints
    Route::get('/whypazars/all', [WhyPazarController::class, 'all']);
    Route::apiResource('whypazars', WhyPazarController::class);
    
    // Career module endpoints (departments, experiences, vacancies, work at pazar, career info, career page)
    Route::get('/departments/all', [DepartmentController::class, 'all']);
    Route::apiResource('departments', DepartmentController::class);
    Route::get('/experiences/all', [ExperienceController::class, 'all']);
    Route::apiResource('experiences', ExperienceController::class);
    Route::get('/vacancies/all', [VacancyController::class, 'all']);
    // Define the active vacancies route BEFORE the resource route
    Route::get('/vacancies/active', [VacancyController::class, 'getActiveVacancies']);
    Route::apiResource('vacancies', VacancyController::class);
    Route::get('vacancies/getVacancyDetail/{identifier}', [VacancyController::class, 'getVacancyDetail']);
    Route::get('vacancies/getRelatedVacancies', [VacancyController::class, 'getRelatedVacancies']);
    Route::get('/vacancies/check-expired', [VacancyController::class, 'checkExpiredVacancies']);
    Route::get('/workatpazars/all', [WorkAtPazarController::class, 'all']);
    Route::apiResource('workatpazars', WorkAtPazarController::class);
    Route::get('/workatpazars/type/{type}', [WorkAtPazarController::class, 'getByType']);
    Route::get('/careerinfos/all', [CareerInfoController::class, 'all']);
    Route::apiResource('careerinfos', CareerInfoController::class);
    Route::get('/career/data', [CareerController::class, 'getIndexData']);
});