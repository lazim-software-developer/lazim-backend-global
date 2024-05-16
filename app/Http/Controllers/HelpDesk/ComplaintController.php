<?php

namespace App\Http\Controllers\HelpDesk;

use App\Http\Controllers\Controller;
use App\Http\Requests\Helpdesk\ComplaintStoreRequest;
use App\Http\Requests\Helpdesk\ComplaintUpdateRequest;
use App\Http\Requests\IncidentRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\HelpDesk\Complaintresource;
use App\Models\Building\Building;
use App\Models\Building\BuildingPoc;
use App\Models\Building\Complaint;
use App\Models\Building\FlatTenant;
use App\Models\ExpoPushNotification;
use App\Models\Master\Service;
use App\Models\Media;
use App\Models\TechnicianVendor;
use App\Models\User\User;
use App\Models\Vendor\Contract;
use App\Models\Vendor\ServiceVendor;
use App\Traits\UtilsTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ComplaintController extends Controller
{
    use UtilsTrait;

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

    public function createIncident(IncidentRequest $request, Building $building){
        // if (auth()->user()->role->name == 'Security') {
        //     // Check if the gatekeeper has active member of the building
        //     $complaintableClass =  User::class;
        //     $complaitableId = auth()->user()->id;

        //     $isActiveSecurity = BuildingPoc::where([
        //         'user_id' => auth()->user()->id,
        //         'role_name' => 'security',
        //         'building_id' => $building->id,
        //         'active' => 1
        //     ])->exists();

        //     if (!$isActiveSecurity) {
        //         return (new CustomResponseResource([
        //             'title' => 'Error',
        //             'message' => 'You are not allowed to post a complaint.',
        //             'code' => 403,
        //         ]))->response()->setStatusCode(403);
        //     }
        // }
        $complaintableClass =  User::class;
            $complaitableId = 3;
            $request->merge([
                'complaintable_type' => $complaintableClass,
                'complaintable_id' => $complaitableId,
                'user_id' => 3,
                'category' => 'Security Services',
                'open_time' => now(),
                'status' => 'open',
                'building_id' => $building->id,
                'owner_association_id' => $building->owner_association_id,
            ]);

            return $complaint = Complaint::withoutEvents(function () use ($request) {
                return Complaint::create($request->all());
            });
    }

    /**
     * Show the form for creating a new complaint.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(ComplaintStoreRequest $request, Building $building)
    {
        if (auth()->user()->role->name == 'Security') {
            // Check if the gatekeeper has active member of the building
            $complaintableClass =  User::class;
            $complaitableId = auth()->user()->id;

            $isActiveSecurity = BuildingPoc::where([
                'user_id' => auth()->user()->id,
                'role_name' => 'security',
                'building_id' => $building->id,
                'active' => 1
            ])->exists();

            if (!$isActiveSecurity) {
                return (new CustomResponseResource([
                    'title' => 'Error',
                    'message' => 'You are not allowed to post a complaint.',
                    'code' => 403,
                ]))->response()->setStatusCode(403);
            }
        } else {
            // Check if the tenant is a active resident of the building
            // Fetch the flat_tenant ID using the building_id, logged-in user's ID, and active status
            $flatTenant = FlatTenant::where([
                'flat_id' => $request->flat_id,
                'tenant_id' => auth()->user()->id,
                'active' => 1
            ])->first();

            $complaintableClass = FlatTenant::class;
            $complaitableId = $flatTenant->id;

            if (!$flatTenant) {
                return (new CustomResponseResource([
                    'title' => 'Error',
                    'message' => 'You are not allowed to post a complaint.',
                    'code' => 403,
                ]))->response()->setStatusCode(403);
            }
        }

        $categoryName = $request->category ? Service::where('id', $request->category)->value('name') : '';

        $service_id = $request->category ?? null;

        // Fetch vendor id who is having an active contract for the given service in the building
        $vendor = ServiceVendor::where([
            'building_id' => $building->id, 'service_id' => $service_id, 'active' => 1
        ])->first();

        $request->merge([
            'complaintable_type' => $complaintableClass,
            'complaintable_id' => $complaitableId,
            'user_id' => auth()->user()->id,
            'category' => $categoryName,
            'open_time' => now(),
            'status' => 'open',
            'building_id' => $building->id,
            'owner_association_id' => $building->owner_association_id,
        ]);

        // assign a vendor if the complaint type is tenant_complaint or help_desk
        if ($request->complaint_type == 'tenant_complaint' || $request->complaint_type == 'help_desk' || $request->complaint_type == 'snag') {
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

        // Assign complaint to a technician
        // AssignTechnicianToComplaint::dispatch($complaint);
        $serviceId = $complaint->service_id;
        $buildingId = $complaint->building_id;

        $contract = Contract::where('service_id', $serviceId)->where('building_id', $buildingId)->where('end_date', '>=', Carbon::now()->toDateString())->first();
        if ($contract) {
            // Fetch technician_vendor_ids for the given service
            $technicianVendorIds = DB::table('service_technician_vendor')
                ->where('service_id', $contract->service_id)
                ->pluck('technician_vendor_id');

            $vendorId = $contract->vendor_id;

            // Fetch technicians who are active and match the service
            $technicianIds = TechnicianVendor::whereIn('id', $technicianVendorIds)
                ->where('active', true)->where('vendor_id', $vendorId)
                ->pluck('technician_id');
            $assignees = User::whereIn('id', $technicianIds)
                ->withCount(['assignees' => function ($query) {
                    $query->where('status', 'open');
                }])
                ->orderBy('assignees_count', 'asc')
                ->get();
            $selectedTechnician = $assignees->first();

            if ($selectedTechnician) {
                $complaint->technician_id = $selectedTechnician->id;
                $complaint->save();

                $expoPushToken = ExpoPushNotification::where('user_id', $selectedTechnician->id)->first()?->token;
                if ($expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Task Assigned',
                        'body' => 'Task has been assigned',
                        'data' => ['notificationType' => 'PendingRequests'],
                    ];
                    $this->expoNotification($message);
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $selectedTechnician->id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Task has been assigned',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Task Assigned',
                            'view' => 'notifications::notification',
                            'viewData' => [],
                            'format' => 'filament',
                            'url' => 'PendingRequests',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
                }
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

        if($complaint->user_id != auth()->user()->id){

            $expoPushToken = ExpoPushNotification::where('user_id', $complaint->user_id)->first()?->token;
                if ($expoPushToken) {
                    if ($complaint->complaint_type == 'help_desk'){
                            $notificationType = 'HelpDeskTabResolved';
                    }
                    elseif ($complaint->complaint_type == 'snag'){
                        
                            $notificationType = 'MyComplaints';
                    }
                    else{
                        $notificationType = 'InAppNotficationScreen';
                    }
                        $message = [
                            'to' => $expoPushToken,
                            'sound' => 'default',
                            'title' => 'Complaint status',
                            'body' => 'Your complaint has been resolved by : '.auth()->user()->role->name.' '.auth()->user()->first_name,
                            'data' => ['notificationType' => $notificationType],
                        ];
                        $this->expoNotification($message);
                        DB::table('notifications')->insert([
                            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                            'type' => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => 'App\Models\User\User',
                            'notifiable_id' => $complaint->user_id,
                            'data' => json_encode([
                                'actions' => [],
                                'body' => 'Your complaint has been resolved by : '.auth()->user()->role->name.' '.auth()->user()->first_name,
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => 'Complaint status',
                                'view' => 'notifications::notification',
                                'viewData' => [],
                                'format' => 'filament',
                                'url' => $notificationType,
                            ]),
                            'created_at' => now()->format('Y-m-d H:i:s'),
                            'updated_at' => now()->format('Y-m-d H:i:s'),
                        ]);
                    
                }
        }
        else{
            $expoPushToken = ExpoPushNotification::where('user_id', $complaint->technician_id)->first()?->token;
                if ($expoPushToken) {
                        $notificationType = 'ResolvedRequests';
                        $message = [
                            'to' => $expoPushToken,
                            'sound' => 'default',
                            'title' => 'Complaint status',
                            'body' => 'A complaint has been resolved by : '.auth()->user()->role->name.' '.auth()->user()->first_name,
                            'data' => ['notificationType' => $notificationType],
                        ];
                        $this->expoNotification($message);
                        DB::table('notifications')->insert([
                            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                            'type' => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => 'App\Models\User\User',
                            'notifiable_id' => $complaint->technician_id,
                            'data' => json_encode([
                                'actions' => [],
                                'body' => 'A complaint has been resolved by : '.auth()->user()->role->name.' '.auth()->user()->first_name,
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => 'Complaint status',
                                'view' => 'notifications::notification',
                                'viewData' => [],
                                'format' => 'filament',
                                'url' => 'ResolvedRequests',
                            ]),
                            'created_at' => now()->format('Y-m-d H:i:s'),
                            'updated_at' => now()->format('Y-m-d H:i:s'),
                        ]);
                    
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
