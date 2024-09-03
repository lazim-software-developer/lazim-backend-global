<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Http\Resources\Technician\ComplaintResource;
use App\Models\Building\Complaint;
use Illuminate\Http\Request;

class TasksController extends Controller
{
    public function index(Request $request)
    {
        $complaints = Complaint::where('technician_id', auth()->user()->id)
            ->where(function ($query) {
                $query->where('complaint_type', 'tenant_complaint')
                ->orWhere('complaint_type', 'help_desk')
                ->orWhere('complaint_type', 'snag')
                ->orWhere('complaint_type', 'oa_complaint_report');
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(10);

        return ComplaintResource::collection($complaints);
    }
}
