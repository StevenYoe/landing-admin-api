<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Header;
use App\Models\WorkAtPazar;
use App\Models\CareerInfo;

// Controller for handling career info page related API endpoints
class CareerController extends Controller
{
    /**
     * Get all data needed for the career info page
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getIndexData(Request $request)
    {
        try {
            // Get header specifically for the career info page
            $header = Header::where('h_page_name', 'careerinfo')->first();
            
            // Fallback to first header if none found with the specific page name
            if (!$header) {
                $header = Header::first();
            }
            
            // Get 'Work at Pazar' sections by their types (work, why, join)
            $workAtPazarWork = WorkAtPazar::where('wap_type', 'work')->first();
            $workAtPazarWhy = WorkAtPazar::where('wap_type', 'why')->first();
            $workAtPazarJoin = WorkAtPazar::where('wap_type', 'join')->first();
            
            // Get all career info items for the page
            $careerInfos = CareerInfo::all();
            
            // Aggregate all career page data into a single array
            $careerData = [
                'header' => $header,
                'work_at_pazar_work' => $workAtPazarWork,
                'work_at_pazar_why' => $workAtPazarWhy,
                'work_at_pazar_join' => $workAtPazarJoin,
                'career_infos' => $careerInfos
            ];
            
            // Return the career data as a JSON response
            return response()->json([
                'success' => true,
                'data' => $careerData
            ]);
        } catch (\Exception $e) {
            // Handle any exceptions and return an error response
            return response()->json([
                'success' => false,
                'message' => 'Error fetching career data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}