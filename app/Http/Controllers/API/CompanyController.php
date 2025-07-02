<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Header;
use App\Models\CompanyProfile;
use App\Models\History;

class CompanyController extends Controller
{
    /**
     * Get all data needed for the company page
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompanyData(Request $request)
    {
        try {
            // Get header specifically for the company page
            $header = Header::where('h_page_name', 'company')->first();
            
            // Fallback to first header if none found with the specific page name
            if (!$header) {
                $header = Header::first();
            }
            
            // Get company profile sections by types
            $companyWhat = CompanyProfile::where('cp_type', 'what')->first();
            $companyPolicy = CompanyProfile::where('cp_type', 'policy')->first();
            $companyVision = CompanyProfile::where('cp_type', 'vision')->first();
            $companyMission = CompanyProfile::where('cp_type', 'mission')->first();
            
            // Get all histories ordered by year
            $histories = History::orderBy('hs_year', 'asc')->get();
            
            $companyData = [
                'header' => $header,
                'company_what' => $companyWhat,
                'histories' => $histories,
                'company_policy' => $companyPolicy,
                'company_vision' => $companyVision,
                'company_mission' => $companyMission
            ];
            
            return response()->json([
                'success' => true,
                'data' => $companyData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching company data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}