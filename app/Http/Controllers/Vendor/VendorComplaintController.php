<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\StoreCommentRequest;
use App\Http\Resources\Community\CommentResource;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Vendor\VendorComplaintsResource;
use App\Models\Building\Building;
use App\Models\Building\Complaint;
use App\Models\BuildingVendor;
use App\Models\Community\Comment;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorComplaintController extends Controller
{
    public function listComplaints(Request $request,Vendor $vendor)
    {
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
            $query->whereIn('complaint_type', ['help_desk', 'tenant_complaint','snag']);
        })
        ->whereBetween('updated_at', [$start_date, $end_date])
        ->latest()->paginate(10);

        return VendorComplaintsResource::collection($complaints);
    }

    public function addComment(StoreCommentRequest $request, Complaint $complaint)
    {
        $comment = new Comment([
            'body' => $request->body,
            'user_id' => auth()->user()->id,
        ]);

        $complaint->comments()->save($comment);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => "Comment added successfully",
            'errorCode' => 201,
            'status' => 'success',
            'data' => new CommentResource($comment)
        ]))->response()->setStatusCode(201);
    }
}
