<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Header;
use App\Models\Certification;
use App\Models\WhyPazar;
use App\Models\Testimonial;

// Controller for handling brand page related API endpoints
class BrandController extends Controller
{
    /**
     * Get all data needed for the brand page
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBrandData(Request $request)
    {
        try {
            // Get header specifically for the brand page
            $header = Header::where('h_page_name', 'brand')->first();
            
            // Fallback to first header if none found with the specific page name
            if (!$header) {
                $header = Header::first();
            }
            
            // Get all certifications for the brand page
            $certifications = Certification::all();
            
            // Get all 'Why Pazar' items for the brand page
            $whyPazarItems = WhyPazar::all();
            
            // Get testimonials by type (customer and chef) with complete profile information
            $customerTestimonials = Testimonial::where('t_type', 'customer')->get();
            $chefTestimonials = Testimonial::where('t_type', 'chef')->get();
            
            // Aggregate all brand page data into a single array
            $brandData = [
                'header' => $header,
                'certifications' => $certifications,
                'why_pazar_items' => $whyPazarItems,
                'testimonials' => [
                    'customer' => $customerTestimonials,
                    'chef' => $chefTestimonials
                ]
            ];
            
            // Return the brand data as a JSON response
            return response()->json([
                'success' => true,
                'data' => $brandData
            ]);
        } catch (\Exception $e) {
            // Handle any exceptions and return an error response
            return response()->json([
                'success' => false,
                'message' => 'Error fetching brand data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}