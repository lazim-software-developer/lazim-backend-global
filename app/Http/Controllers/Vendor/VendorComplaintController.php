<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\StoreCommentRequest;
use App\Http\Resources\Community\CommentResource;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Vendor\VendorComplaintsResource;
use App\Models\Building\Complaint;
use App\Models\BuildingVendor;
use App\Models\Community\Comment;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorComplaintController extends Controller
{
    public function listComplaints(Request $request)
    {
        $vendorId =Vendor::where('owner_id', auth()->user()->id)->first()->id;

        $buildingIds= BuildingVendor::where('vendor_id', $vendorId)->where('active',true)->pluck('building_id');

        $complaints = Complaint::whereIn('building_id', $buildingIds)->where('complaintable_type','App\Models\Vendor\Vendor');

        return VendorComplaintsResource::collection($complaints->paginate($request->paginate ?? $complaints->count()));
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
