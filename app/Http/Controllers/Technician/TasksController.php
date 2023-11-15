<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Http\Controllers\HelpDesk\ComplaintController;
use App\Http\Resources\HelpDesk\Complaintresource;
use App\Models\Building\Complaint;
use Illuminate\Http\Request;

class TasksController extends Controller
{
    public function index() {
        $tasks = Complaint::where('technician_id', auth()->user()->id)->where('type', 'tenant_complaint')->latest()->get();

        return Complaintresource::collection($tasks);
    }
}
