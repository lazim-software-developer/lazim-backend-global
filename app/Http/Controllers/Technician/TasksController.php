<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Http\Resources\Technician\ComplaintResource;
use App\Models\Building\Complaint;

class TasksController extends Controller
{
    public function index()
    {
        $complaints = Complaint::where('technician_id', auth()->user()->id)
            ->where(function ($query) {
                $query->where('complaint_type', 'tenant_complaint')
                ->orWhere('complaint_type', 'help_desk')
                ->orWhere('complaint_type', 'snags');
            })
            ->latest()
            ->paginate(10);

        return ComplaintResource::collection($complaints);
    }
}
