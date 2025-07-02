<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Header;
use App\Models\CompanyProfile;
use App\Models\History;

// Controller for handling company page related API endpoints
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
            
            // Get company profile sections by their types (what, policy, vision, mission)
            $companyWhat = CompanyProfile::where('cp_type', 'what')->first();
            $companyPolicy = CompanyProfile::where('cp_type', 'policy')->first();
            $companyVision = CompanyProfile::where('cp_type', 'vision')->first();
            $companyMission = CompanyProfile::where('cp_type', 'mission')->first();
            
            // Get all company history records ordered by year
            $histories = History::orderBy('hs_year', 'asc')->get();
            
            // Aggregate all company page data into a single array
            $companyData = [
                'header' => $header,
                'company_what' => $companyWhat,
                'histories' => $histories,
                'company_policy' => $companyPolicy,
                'company_vision' => $companyVision,
                'company_mission' => $companyMission
            ];
            
            // Return the company data as a JSON response
            return response()->json([
                'success' => true,
                'data' => $companyData
            ]);
        } catch (\Exception $e) {
            // Handle any exceptions and return an error response
            return response()->json([
                'success' => false,
                'message' => 'Error fetching company data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}