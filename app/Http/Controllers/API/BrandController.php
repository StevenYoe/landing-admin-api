<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Header;
use App\Models\Certification;
use App\Models\WhyPazar;
use App\Models\Testimonial;

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
            
            // Get all certifications
            $certifications = Certification::all();
            
            // Get all "Why Pazar" items
            $whyPazarItems = WhyPazar::all();
            
            // Get testimonials by type with complete profile information
            $customerTestimonials = Testimonial::where('t_type', 'customer')->get();
            $chefTestimonials = Testimonial::where('t_type', 'chef')->get();
            
            $brandData = [
                'header' => $header,
                'certifications' => $certifications,
                'why_pazar_items' => $whyPazarItems,
                'testimonials' => [
                    'customer' => $customerTestimonials,
                    'chef' => $chefTestimonials
                ]
            ];
            
            return response()->json([
                'success' => true,
                'data' => $brandData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching brand data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}