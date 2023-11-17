<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Vendor\SnagStatsResource;
use App\Models\Building\Complaint;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;

class SnagDashboardController extends Controller
{
    public function tasks(Request $request,Vendor $vendor){

        $end_date = now();
        $start_date = now()->subDays(7);
        if ($request->duration == 'lastMonth'){
            $start_date = now()->subDays(30);
        };
        if ($request->duration == 'custom'){
            $end_date = $request->end_date;
            $start_date = $request->start_date;
        }
        $complaints = Complaint::where('vendor_id', $vendor->id)
        ->when($request->filled('building_id'), function ($query) use ($request) {
            $query->where('building_id', $request->building_id);
        })
        ->when($request->filled('complaint_type'), function ($query) use ($request) {
            $query->where('complaint_type', $request->complaint_type);
        },function ($query) {
            // Default to 'help_desk' and 'tenant_complaint' if complaint_type is not sent
            $query->whereIn('complaint_type', ['help_desk', 'tenant_complaint']);
        })
        ->whereBetween('updated_at', [$start_date, $end_date])->get();

        return new SnagStatsResource($complaints);
        
    }
}
