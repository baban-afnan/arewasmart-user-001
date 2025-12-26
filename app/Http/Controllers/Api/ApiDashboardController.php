<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ApiApplication; // Assuming you have a model for this table
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApiDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userRole = $user->role; // Get the authenticated user's role

        // Fetch services that are active with their active fields
        $services = Service::where('is_active', true)
            ->with(['fields' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get();

        // Check for existing application status
        $application = DB::table('api_applications')
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        return view('api.dashboard', compact('services', 'application', 'userRole'));
    }

    public function apply(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'website_link' => 'required|url',
            'business_description' => 'required|string|min:10',
            'business_nature' => 'required|string',
            'terms' => 'accepted',
        ]);

        // Check for existing application (any status)
        $existingApplication = DB::table('api_applications')
            ->where('user_id', Auth::id())
            ->first();

        if ($existingApplication && in_array($existingApplication->status, ['pending', 'approved'])) {
             return back()->with('error', 'You already have a pending or approved application.');
        }

        if ($existingApplication) {
            // Update existing application
            DB::table('api_applications')
                ->where('id', $existingApplication->id)
                ->update([
                    'api_type' => 'business',
                    'business_name' => $request->business_name,
                    'website_link' => $request->website_link,
                    'business_description' => $request->business_description,
                    'business_nature' => $request->business_nature,
                    'status' => 'pending',
                    'updated_at' => now(),
                ]);
                
            $message = 'Your application has been updated and resubmitted successfully.';
        } else {
            // Create new application
            DB::table('api_applications')->insert([
                'user_id' => Auth::id(),
                'api_type' => 'business',
                'business_name' => $request->business_name,
                'website_link' => $request->website_link,
                'business_description' => $request->business_description,
                'business_nature' => $request->business_nature,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $message = 'Your application has been submitted successfully feedback will be provided soon.';
        }

        return back()->with('success', $message);
    }
}
