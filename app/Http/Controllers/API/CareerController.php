<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Header;
use App\Models\WorkAtPazar;
use App\Models\CareerInfo;

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
            // Get header specifically for the career page
            $header = Header::where('h_page_name', 'careerinfo')->first();
            
            // Fallback to first header if none found with the specific page name
            if (!$header) {
                $header = Header::first();
            }
            
            // Get work at pazar sections by types
            $workAtPazarWork = WorkAtPazar::where('wap_type', 'work')->first();
            $workAtPazarWhy = WorkAtPazar::where('wap_type', 'why')->first();
            $workAtPazarJoin = WorkAtPazar::where('wap_type', 'join')->first();
            
            // Get all career info items
            $careerInfos = CareerInfo::all();
            
            $careerData = [
                'header' => $header,
                'work_at_pazar_work' => $workAtPazarWork,
                'work_at_pazar_why' => $workAtPazarWhy,
                'work_at_pazar_join' => $workAtPazarJoin,
                'career_infos' => $careerInfos
            ];
            
            return response()->json([
                'success' => true,
                'data' => $careerData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching career data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}