<?php

namespace App\Http\Controllers\HelpDesk;

use App\Http\Controllers\Controller;
use App\Http\Requests\Helpdesk\ComplaintStoreRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\HelpDesk\Complaintresource;
use App\Models\Building\Building;
use App\Models\Building\Complaint;
use App\Models\Building\FlatTenant;
use App\Models\Media;
use App\Models\Tag;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    /**
     * Display a listing of the complaints.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Building $building)
    {
        $query = Complaint::where([
            'user_id' => auth()->user()->id,
            'building_id' => $building->id,
            'complaint_type' => $request->type,
            'owner_association_id' => $building->owner_association_id
        ]);

        // Filter based on status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Get the complaints in latest first order
        $complaints = $query->latest()->paginate(10);

        return ComplaintResource::collection($complaints);
    }

    /**
     * Display a comaplaint and details about the complaint.
     *
     * @param  Complaint  $complaint
     * @return \Illuminate\Http\Response
     */
    public function show(Complaint $complaint)
    {
        $this->authorize('view', $complaint);

        $complaint->load('comments');
        return new Complaintresource($complaint);
    }

    /**
     * Show the form for creating a new complaint.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(ComplaintStoreRequest $request, Building $building)
    {
        // Fetch the flat_tenant ID using the building_id, logged-in user's ID, and active status
        $flatTenant = FlatTenant::where([
            'building_id' => $building->id,
            'tenant_id' => auth()->user()->id,
            'active' => 1
        ])->first();

        if (!$flatTenant) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'You are not allowed to post a complaint.',
                'errorCode' => 403,
            ]))->response()->setStatusCode(403);
        }

        $category = $request->category ? Tag::where('id', $request->category)->value('name') : '';

        // Create the complaint
        $complaint = Complaint::create([
            'complaint' => $request->complaint,
            'complaint_type' => $request->complaint_type,
            'complaintable_type' => FlatTenant::class,
            'complaintable_id' => $flatTenant->id,
            'user_id' => auth()->user()->id,
            'category' => $category,
            'open_time' => now(),
            'status' => 'open',
            'building_id' => $building->id,
            'owner_association_id' => $building->owner_association_id,
            'complaint_details' => $request->complaint_details ?? null
        ]);

        // Save images in media table with name "before". Once resolved, we'll store media with "after" name
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = optimizeAndUpload($image, 'dev');

                // Create a new media entry for each image
                $media = new Media([
                    'name' => "before",
                    'url' => $imagePath,
                ]);

                // Attach the media to the post
                $complaint->media()->save($media);
            }
        }

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => "We'll get back to you at the earliest!",
            'errorCode' => 201,
        ]))->response()->setStatusCode(201);
    }

    public function resolve(Request $request, Complaint $complaint)
    {
        $complaint->update([
            'status' => 'closed',
            'close_time' => now(),
            'closed_by' => auth()->user()->id,
            'remarks' => $request->remarks
        ]);

        // Save images in media table with name "after".
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = optimizeAndUpload($image, 'dev');

                // Create a new media entry for each image
                $media = new Media([
                    'name' => "after",
                    'url' => $imagePath,
                ]);

                // Attach the media to the post
                $complaint->media()->save($media);
            }
        }

        return new CustomResponseResource([
            'title' => 'Complaint Resolved',
            'message' => 'The complaint has been marked as resolved.',
        ]);
    }
}
