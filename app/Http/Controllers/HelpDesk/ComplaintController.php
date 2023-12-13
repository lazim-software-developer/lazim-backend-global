<?php

namespace App\Http\Controllers\HelpDesk;

use App\Http\Controllers\Controller;
use App\Http\Requests\Helpdesk\ComplaintStoreRequest;
use App\Http\Requests\Helpdesk\ComplaintUpdateRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\HelpDesk\Complaintresource;
use App\Jobs\AssignTechnicianToComplaint;
use App\Models\Building\Building;
use App\Models\Building\Complaint;
use App\Models\Building\FlatTenant;
use App\Models\Master\Service;
use App\Models\Media;
use App\Models\Tag;
use App\Models\TechnicianVendor;
use App\Models\User\User;
use App\Models\Vendor\Contract;
use App\Models\Vendor\ServiceVendor;
use App\Models\Vendor\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            'flat_id' => $request->flat_id,
            'tenant_id' => auth()->user()->id,
            'active' => 1
        ])->first();

        if (!$flatTenant) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'You are not allowed to post a complaint.',
                'code' => 403,
            ]))->response()->setStatusCode(403);
        }

        $categoryName = $request->category ? Service::where('id', $request->category)->value('name') : '';

        $service_id = $request->category ?? null;


        // Fetch vendor id who is having an active contract for the given service in the building
        $vendor = ServiceVendor::where([
            'building_id' => $building->id, 'service_id' => $service_id, 'active' => 1
        ])->first();

        $request->merge([
            'complaintable_type' => FlatTenant::class,
            'complaintable_id' => $flatTenant->id,
            'user_id' => auth()->user()->id,
            'category' => $categoryName,
            'open_time' => now(),
            'status' => 'open',
            'building_id' => $building->id,
            'owner_association_id' => $building->owner_association_id,
        ]);

        // assign a vendor if the complaint type is tenant_complaint or help_desk
        if($request->complaint_type == 'tenant_complaint' || $request->complaint_type == 'help_desk') {
            $request->merge([
                'priority' => 3,
                'due_date' => now()->addDays(3),
                'service_id' => $service_id,
                'vendor_id' => $vendor ? $vendor->vendor_id : null
            ]);
        }

        // Create the complaint and assign it the vendor
        // TODO: Assign ticket automatically to technician
        $complaint = Complaint::create($request->all());

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
        // return $complaint;
        // Assign complaint to a technician
        // AssignTechnicianToComplaint::dispatch($complaint);
        $serviceId = $complaint->service_id;
        $buildingId = $complaint->building_id;

        $contract = Contract::where('service_id', $serviceId)->where('building_id', $buildingId)->where('end_date','>=', Carbon::now()->toDateString())->first();
        if ($contract){
            // Fetch technician_vendor_ids for the given service
            $technicianVendorIds = DB::table('service_technician_vendor')
                                     ->where('service_id', $contract->service_id)
                                     ->pluck('technician_vendor_id');

            $vendorId = $contract->vendor_id;

            // Fetch technicians who are active and match the service
            $technicianIds = TechnicianVendor::whereIn('id', $technicianVendorIds)
                                          ->where('active', true)->where('vendor_id',$vendorId)
                                          ->pluck('technician_id');
            $assignees = User::whereIn('id',$technicianIds)
                                ->withCount(['assignees' => function ($query) {
                                        $query->where('status', 'open');
                                    }])
                                    ->orderBy('assignees_count', 'asc')
                                    ->get();
            $selectedTechnician = $assignees->first();

            if ($selectedTechnician) {
                $complaint->technician_id = $selectedTechnician->id;
                $complaint->save();
            } else {
                Log::info("No technicians to add", []);
            }
        }
            return (new CustomResponseResource([
                'title' => 'Success',
                'message' => "We'll get back to you at the earliest!",
                'code' => 201,
                'status' => 'success',
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

    public function update(ComplaintUpdateRequest $request, Complaint $complaint)
    {
        $request['technician_id'] = TechnicianVendor::find($request->technician_id)->technician_id;
        $complaint->update($request->all());
        return (new CustomResponseResource([
            'title' => 'Complaint Updated Successfully',
            'message' => 'The complaint has been updated.',
            'code' => 200,
            'status' => 'success',
            'data'  => $complaint,
        ]))->response()->setStatusCode(200);
    }
}
