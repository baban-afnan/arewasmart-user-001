<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserEnrollment;

class EnrolmentReportController extends Controller
{
    public function index(Request $request)
    {
        $query = UserEnrollment::query();
        $stats = [
            'total' => 0,
            'successful' => 0,
            'failed' => 0,
            'ongoing' => 0,
            'agent_name' => null
        ];

        // Only query if agent_code or search is provided
        if ($request->filled('agent_code') || $request->filled('search')) {
            // Filter by Agent Code
            if ($request->filled('agent_code')) {
                $query->where('agent_code', $request->agent_code);
                
                // Calculate stats for this agent
                $statsQuery = UserEnrollment::where('agent_code', $request->agent_code);
                
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $statsQuery->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
                }

                $stats['total'] = $statsQuery->count();
                $stats['successful'] = $statsQuery->clone()->where('validation_status', 'successful')->count();
                $stats['failed'] = $statsQuery->clone()->where('validation_status', 'failed')->count();
                $stats['ongoing'] = $statsQuery->clone()->whereIn('validation_status', ['pending', 'processing'])->count();
                
                $agent = $statsQuery->first();
                if ($agent) {
                    $stats['agent_name'] = $agent->agent_name;
                }
            }

            // Filter by Date Range for the list
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
            }

            // Search by Ticket ID
            if ($request->filled('search')) {
                $query->where('ticket_number', 'like', '%' . $request->search . '%');
            }

            $enrollments = $query->latest()->paginate(15)->withQueryString();
        } else {
            // Return empty paginator if no filter is applied
            $enrollments = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        }

        return view('bvn.enrolment-report', compact('enrollments', 'stats'));
    }
}
