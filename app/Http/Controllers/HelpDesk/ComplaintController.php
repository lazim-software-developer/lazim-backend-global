<?php

namespace App\Http\Controllers\HelpDesk;

use App\Filament\Resources\IncidentResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Helpdesk\ComplaintStoreRequest;
use App\Http\Requests\Helpdesk\ComplaintUpdateRequest;
use App\Http\Requests\IncidentRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\HelpDesk\Complaintresource;
use App\Http\Resources\Vendor\VendorComplaintsResource;
use App\Jobs\Complaint\ComplaintCreationJob;
use App\Models\AccountCredentials;
use App\Models\Building\Building;
use App\Models\Building\BuildingPoc;
use App\Models\Building\Complaint;
use App\Models\Building\FlatTenant;
use App\Models\ExpoPushNotification;
use App\Models\Master\Role;
use App\Models\Master\Service;
use App\Models\Media;
use App\Models\OwnerAssociation;
use App\Models\TechnicianVendor;
use App\Models\User\User;
use App\Models\Vendor\Contract;
use App\Models\Vendor\ServiceVendor;
use App\Traits\UtilsTrait;
use Carbon\Carbon;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
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
        $flats = FlatTenant::where('tenant_id', auth()->user()->id)
            ->where('active', 1)
            ->pluck('flat_id');

        // First query for user's direct complaints
        $query = Complaint::where([
            'building_id'    => $building->id,
            'complaint_type' => $request->type,
        ])->where(function($q) use ($flats) {
            $q->where('user_id', auth()->user()->id)
              ->orWhereIn('flat_id', $flats);
        });

        // Filter based on status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Get the complaints in latest first order
        $complaints = $query->latest()->paginate($request->paginate ?? 10);

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
        // $this->authorize('view', $complaint);

        $complaint->load('comments');
        return new Complaintresource($complaint);
    }

    public function createIncident(IncidentRequest $request, Building $building)
    {
        if ($building->id) {
            DB::table('building_owner_association')
                ->where(['building_id' => $building->id, 'active' => true])->first()->owner_association_id;
        }

        if (auth()->user()->role->name == 'Security') {
            // Check if the gatekeeper has active member of the building
            $complaintableClass = User::class;
            $complaitableId     = auth()->user()->id;

            $isActiveSecurity = BuildingPoc::where([
                'user_id'     => auth()->user()->id,
                'role_name'   => 'security',
                'building_id' => $building->id,
                'active'      => 1,
            ])->exists();

            if (!$isActiveSecurity) {
                return (new CustomResponseResource([
                    'title'   => 'Error',
                    'message' => 'You are not allowed to post a complaint.',
                    'code'    => 403,
                ]))->response()->setStatusCode(403);
            }
        }
        // $categoryName = $request->category ? Service::where('id', $request->category)->value('name') : '';

        // $service_id = $request->category ?? null;

        // Fetch vendor id who is having an active contract for the given service in the building
        // $vendor = ServiceVendor::where([
        //     'building_id' => $building->id, 'service_id' => $service_id, 'active' => 1,
        // ])->first();

        $request->merge([
            'complaintable_type'   => $complaintableClass,
            'complaintable_id'     => $complaitableId,
            'user_id'              => auth()->user()->id,
            'category'             => 'Incidents',
            'open_time'            => now(),
            'status'               => 'open',
            'building_id'          => $building->id,
            'owner_association_id' => $building->owner_association_id,
        ]);

        // assign a vendor if the complaint type is tenant_complaint or help_desk
        // if ($request->complaint_type == 'incident') {
        //     $request->merge([
        //         'priority'   => 3,
        //         // 'due_date'   => now()->addDays(3),
        //         // 'service_id' => $service_id,
        //         // 'vendor_id'  => $vendor ? $vendor->vendor_id : null,
        //     ]);
        // }

        // Create the complaint and assign it the vendor
        // TODO: Assign ticket automatically to technician
        $complaint = Complaint::withoutEvents(function () use ($request) {
            return Complaint::create($request->all());
        });

        // Save images in media table with name "before". Once resolved, we'll store media with "after" name
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = optimizeAndUpload($image, 'dev');

                // Create a new media entry for each image
                $media = new Media([
                    'name' => "Incident",
                    'url'  => $imagePath,
                ]);

                // Attach the media to the post
                $complaint->media()->save($media);
            }
        }
        $oa_ids = DB::table('building_owner_association')->where('building_id', $building->id)
            ->where('active', true)->pluck('owner_association_id');
        foreach ($oa_ids as $oa_id) {
            $notifyTo = User::whereHas('role', function ($query) use ($oa_id) {
                $query->whereIn('name', ['OA','Property Manager'])
                      ->where('owner_association_id', $oa_id);
            })
            ->get();
            if($notifyTo->count() > 0){
                foreach($notifyTo as $user){
                    if(!DB::table('notifications')->where('notifiable_id', $user->id)->where('custom_json_data->complaint_id', $complaint?->id)->exists()){
                        $data=[];
                        $data['notifiable_type']='App\Models\User\User';
                        $data['notifiable_id']=$user->id;
                        $slug = OwnerAssociation::where('id',$oa_id)->first()?->slug;
                        if($slug){
                            $data['url']=IncidentResource::getUrl('edit', [$slug, $complaint?->id]);
                        }else{
                            $data['url']=url('/app/incidents/' . $complaint?->id.'/edit');
                        }
                        $data['title']='New Incident';
                        $data['body']='New Incident created by ' . auth()->user()->first_name;
                        $data['building_id']=$complaint->building_id;
                        $data['custom_json_data']=json_encode([
                            'building_id' => $complaint->building_id,
                            'complaint_id' => $complaint->id,
                            'user_id' => auth()->user()->id ?? null,
                            'owner_association_id' => $complaint->owner_association_id,
                            'type' => 'Incident',
                            'priority' => 'Medium',
                        ]);
                        NotificationTable($data);
                    }
                }
            }
                // Notification::make()
                //     ->success()
                //     ->title("New Incident")
                //     ->icon('heroicon-o-document-text')
                //     ->iconColor('warning')
                // ->body('New Incident created!')
                // ->actions([
                //     Action::make('view')
                //         ->button()
                //         ->url(function() use ($oa_id,$complaint){
                //             $slug = OwnerAssociation::where('id',$oa_id)->first()?->slug;
                //             if($slug){
                //                 return IncidentResource::getUrl('edit', [$slug,$complaint?->id]);
                //             }
                //             return url('/app/incidents/' . $complaint?->id.'/edit');
                //         }),
                // ])
                // ->sendToDatabase($notifyTo);
        }

        return (new CustomResponseResource([
            'title'   => 'Success',
            'message' => "We'll get back to you at the earliest!",
            'code'    => 201,
            'status'  => 'success',
        ]))->response()->setStatusCode(201);
    }

    /**
     * Show the form for creating a new complaint.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(ComplaintStoreRequest $request, Building $building)
    {
        $oa_id = DB::table('building_owner_association')->where('building_id', $building->id)->where('active', true)->first()->owner_association_id;

        if (auth()->user()->role->name == 'Security') {
            // Check if the gatekeeper has active member of the building
            $complaintableClass = User::class;
            $complaitableId     = auth()->user()->id;

            $isActiveSecurity = BuildingPoc::where([
                'user_id'     => auth()->user()->id,
                'role_name'   => 'security',
                'building_id' => $building->id,
                'active'      => 1,
            ])->exists();

            if (!$isActiveSecurity) {
                return (new CustomResponseResource([
                    'title'   => 'Error',
                    'message' => 'You are not allowed to post a complaint.',
                    'code'    => 403,
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
            $complaitableId     = $flatTenant->id;



            if (!$flatTenant) {
                return (new CustomResponseResource([
                    'title'   => 'Error',
                    'message' => 'You are not allowed to post a complaint.',
                    'code'    => 403,
                ]))->response()->setStatusCode(403);
            }
        }

        $categoryName = $request->category ? Service::where('id', $request->category)->value('name') : '';

        $service_id = $request->category ?? null;

        // Fetch vendor id who is having an active contract for the given service in the building
        $vendor = ServiceVendor::where([
            'building_id' => $building->id, 'service_id' => $service_id, 'active' => 1,
        ])->first();

        $request->merge([
            'complaintable_type'   => $complaintableClass,
            'complaintable_id'     => $complaitableId,
            'user_id'              => auth()->user()->id,
            'category'             => $categoryName,
            'open_time'            => now(),
            'status'               => 'open',
            'building_id'          => $building->id,
            'owner_association_id' => $building->owner_association_id,
            'ticket_number'        => generate_ticket_number("CP")
        ]);

        // assign a vendor if the complaint type is tenant_complaint or help_desk
        if ($request->complaint_type == 'tenant_complaint' || $request->complaint_type == 'help_desk' || $request->complaint_type == 'snag') {
            $request->merge([
                'priority'   => $request?->urgent != 'false' ? 1 : 3,
                'due_date'   => now()->addDays(3),
                'service_id' => $service_id,
                'vendor_id'  => $vendor ? $vendor->vendor_id : null,
                'type' => $request->type ?: null,
            ]);
        }

        // Create the complaint and assign it the vendor
        // TODO: Assign ticket automatically to technician
        $complaint = Complaint::create($request->all());


        // sending push notification for security

        if( $categoryName == 'Security Services'){

            $isActiveSecurity = BuildingPoc::where([
                'role_name'   => 'security',
                'building_id' => $building->id,
                'active'      => 1,
            ])->first();
            if($isActiveSecurity){
            $expoPushToken = ExpoPushNotification::where('user_id', $isActiveSecurity?->user_id)->first()?->token;
                    if ($expoPushToken) {
                        $message = [
                            'to'    => $expoPushToken,
                            'sound' => 'default',
                            'title' => 'Task Assigned',
                            'body'  => 'Task has been assigned',
                            'data'  => ['notificationType' => 'AssignedToMe'],
                        ];
                        $this->expoNotification($message);
                    }
                        DB::table('notifications')->insert([
                            'id'              => (string) \Ramsey\Uuid\Uuid::uuid4(),
                            'type'            => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => 'App\Models\User\User',
                            'notifiable_id'   => $isActiveSecurity?->user_id,
                            'data'            => json_encode([
                                'actions'   => [],
                                'body'      => 'Task has been assigned',
                                'duration'  => 'persistent',
                                'icon'      => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title'     => 'Task Assigned',
                                'view'      => 'notifications::notification',
                                'viewData'  => [],
                                'format'    => 'filament',
                                'url'       => 'AssignedToMe',
                            ]),
                            'created_at'      => now()->format('Y-m-d H:i:s'),
                            'updated_at'      => now()->format('Y-m-d H:i:s'),
                        ]);
            }
        }
        $credentials = AccountCredentials::where('oa_id', $complaint->owner_association_id)->where('active', true)->latest()->first();
        $mailCredentials = [
            'mail_host' => $credentials->host ?? env('MAIL_HOST'),
            'mail_port' => $credentials->port ?? env('MAIL_PORT'),
            'mail_username' => $credentials->username ?? env('MAIL_USERNAME'),
            'mail_password' => $credentials->password ?? env('MAIL_PASSWORD'),
            'mail_encryption' => $credentials->encryption ?? env('MAIL_ENCRYPTION'),
            'mail_from_address' => $credentials->email ?? env('MAIL_FROM_ADDRESS'),
        ];
        ComplaintCreationJob::dispatch($complaint->id, $technicianId = null, $mailCredentials);

        // Save images in media table with name "before". Once resolved, we'll store media with "after" name
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = optimizeAndUpload($image, 'dev');

                // Create a new media entry for each image
                $media = new Media([
                    'name' => "before",
                    'url'  => $imagePath,
                ]);

                // Attach the media to the post
                $complaint->media()->save($media);
            }
        }

        // Assign complaint to a technician
        // AssignTechnicianToComplaint::dispatch($complaint);
        $serviceId  = $complaint->service_id;
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
                $complaint->vendor_id     = $vendorId;
                $complaint->save();
            } else {
                Log::info("No technicians to add", []);
            }
        }
        return (new CustomResponseResource([
            'title'   => 'Success',
            'message' => "We'll get back to you at the earliest!",
            'code'    => 201,
            'status'  => 'success',
        ]))->response()->setStatusCode(201);
    }

    public function resolve(Request $request, Complaint $complaint)
    {
        $oa_id = DB::table('building_owner_association')->where('building_id', $complaint->building_id)->where('active', true)->first()->owner_association_id;
        $complaint->update([
            'status'     => 'closed',
            'close_time' => $request->has('close_time') ? $request->close_time : now(),
            'closed_by'  => auth()->user()->id,
            'remarks'    => $request->remarks,
        ]);

        // Save images in media table with name "after".
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = optimizeAndUpload($image, 'dev');

                // Create a new media entry for each image
                $media = new Media([
                    'name' => "after",
                    'url'  => $imagePath,
                ]);

                // Attach the media to the post
                $complaint->media()->save($media);
            }
        }

        if ($complaint->user_id != auth()->user()->id) {

            $expoPushToken = ExpoPushNotification::where('user_id', $complaint->user_id)->first()?->token;
            if ($expoPushToken) {
                if ($complaint->complaint_type == 'help_desk') {
                    $notificationType = 'HelpDeskTabResolved';
                } elseif ($complaint->complaint_type == 'snag') {

                    $notificationType = 'MyComplaints';
                } elseif ($complaint->complaint_type == 'preventive_maintenance') {
                    $notificationType = 'PreventiveMaintenance';
                }
                else {
                    $notificationType = 'InAppNotficationScreen';
                }
                $message = [
                    'to'    => $expoPushToken,
                    'sound' => 'default',
                    'title' => ($complaint->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'complaint').' status',
                    'body'  => 'Your '.($complaint->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'complaint').' has been resolved by : ' . auth()->user()->role->name . ' ' . auth()->user()->first_name,
                    'data'  => [
                        'notificationType' => $notificationType,
                        'complaintId'      => $complaint?->id,
                        'open_time' => $complaint?->open_time,
                        'close_time' => $complaint?->close_time,
                        'due_date' => $complaint?->due_date,
                ],
                ];
                $this->expoNotification($message);
                DB::table('notifications')->insert([
                    'id'              => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'type'            => 'Filament\Notifications\DatabaseNotification',
                    'notifiable_type' => 'App\Models\User\User',
                    'notifiable_id'   => $complaint->user_id,
                    'data'            => json_encode([
                        'actions'   => [],
                        'body'      => 'Your '.($complaint->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'complaint').' has been resolved by : ' . auth()->user()->role->name . ' ' . auth()->user()->first_name,
                        'duration'  => 'persistent',
                        'icon'      => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'title'     => ($complaint->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'complaint').' status',
                        'view'      => 'notifications::notification',
                        'viewData'  => [
                            'complaintId'      => $complaint?->id,
                            'open_time' => $complaint?->open_time,
                            'close_time' => $complaint?->close_time,
                            'due_date' => $complaint?->due_date,
                        ],
                        'format'    => 'filament',
                        'url'       => $notificationType,
                    ]),
                    'created_at'      => now()->format('Y-m-d H:i:s'),
                    'updated_at'      => now()->format('Y-m-d H:i:s'),
                ]);
            }
        } else {
            $expoPushToken = ExpoPushNotification::where('user_id', $complaint->technician_id)->first()?->token;
            if ($expoPushToken) {
                $notificationType = 'ResolvedRequests';
                $message          = [
                    'to'    => $expoPushToken,
                    'sound' => 'default',
                    'title' => ($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' status',
                    'body'  => 'A '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance has been completed' : 'complaint has been resolved').' by : ' . auth()->user()->role->name . ' ' . auth()->user()->first_name,
                    'data'  => ['notificationType' => $notificationType,
                        'complaintId'      => $complaint?->id,
                        'open_time' => $complaint?->open_time,
                        'close_time' => $complaint?->close_time,
                        'due_date' => $complaint?->due_date,
                        'building_id' => $complaint?->building_id,
                    ],
                ];
                $this->expoNotification($message);
                DB::table('notifications')->insert([
                    'id'              => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'type'            => 'Filament\Notifications\DatabaseNotification',
                    'notifiable_type' => 'App\Models\User\User',
                    'notifiable_id'   => $complaint->technician_id,
                    'data'            => json_encode([
                        'actions'   => [],
                        'body'      => 'A '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance has been completed' : 'complaint has been resolved').' by : ' . auth()->user()->role->name . ' ' . auth()->user()->first_name,
                        'duration'  => 'persistent',
                        'icon'      => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'title'     => ($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' status',
                        'view'      => 'notifications::notification',
                        'viewData'  => [
                            'complaintId'      => $complaint?->id,
                            'open_time' => $complaint?->open_time,
                            'close_time' => $complaint?->close_time,
                            'due_date' => $complaint?->due_date,
                            'building_id' => $complaint?->building_id,
                        ],
                        'format'    => 'filament',
                        'url'       => 'ResolvedRequests',
                    ]),
                    'created_at'      => now()->format('Y-m-d H:i:s'),
                    'updated_at'      => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }
        if($complaint->complaint_type == 'preventive_maintenance'){
            $residentIds = FlatTenant::where([
                'building_id' => $complaint->building_id,
                'active'      => true,
            ])->distinct()->pluck('tenant_id');
            if ($residentIds->count() > 0) {
                // Create individual notifications for each resident
                foreach ($residentIds as $residentId) {
                    $residentTokens = ExpoPushNotification::where('user_id', $residentId)->first()?->token;
                    $message        = [
                        'to'    => $residentTokens,
                        'sound' => 'default',
                        'title' => 'Preventive Maintenance status',
                        'body'  => 'A '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance has been completed' : 'complaint has been resolved').' by : ' . auth()->user()->role->name . ' ' . auth()->user()->first_name,
                        'data'  => [
                            'notificationType' => 'PreventiveMaintenance',
                            'complaintId'      => $complaint?->id,
                            'open_time'        => $complaint?->open_time,
                            'close_time'       => $complaint?->close_time,
                            'due_date'         => $complaint?->due_date,
                        ],
                    ];
                    $this->expoNotification($message);
                    DB::table('notifications')->insert([
                        'id'              => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type'            => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id'   => $residentId,
                        'data'            => json_encode([
                            'actions'   => [],
                            'body'      => 'A '.($complaint->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance has been completed' : 'complaint has been resolved').' by : ' . auth()->user()->role->name . ' ' . auth()->user()->first_name,
                            'duration'  => 'persistent',
                            'icon'      => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title'     => 'Preventive Maintenance status',
                            'view'      => 'notifications::notification',
                            'viewData'  => [
                                'complaintId' => $complaint?->id,
                                'open_time'   => $complaint?->open_time,
                                'close_time'  => $complaint?->close_time,
                                'due_date'    => $complaint?->due_date,
                            ],
                            'format'    => 'filament',
                            'url'       => 'PreventiveMaintenance',
                        ]),
                        'created_at'      => now()->format('Y-m-d H:i:s'),
                        'updated_at'      => now()->format('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        return new CustomResponseResource([
            'title'   => $complaint->complaint_type == 'preventive_maintenance' ? 'Schedule completed' : 'Complaint Resolved',
            'message' => $complaint->complaint_type == 'preventive_maintenance' ? 'Schedule has been successfully completed' : 'Complaint has been resolved.',
        ]);
    }

    public function update(ComplaintUpdateRequest $request, Complaint $complaint)
    {
        $oa_id = DB::table('building_owner_association')->where('building_id', $complaint->building_id)->where('active', true)->first()->owner_association_id;

        $request['technician_id'] = TechnicianVendor::find($request->technician_id)->technician_id;
        if($request->has('status') && $request->status == 'closed'){
            $request['closed_by'] = auth()->user()->id;
        }
        $categoryName = $request->service_id ? Service::where('id', $request->service_id)->value('name') : '';

        $request->merge([
            'category'             => $categoryName,
        ]);

        $complaint->update($request->all());

        if ($request->hasFile('images') && $request->has('status') && $request->status == 'closed') {
            foreach ($request->file('images') as $image) {
                $imagePath = optimizeAndUpload($image, 'dev');

                // Create a new media entry for each image
                $media = new Media([
                    'name' => "after",
                    'url'  => $imagePath,
                ]);

                // Attach the media to the post
                $complaint->media()->save($media);
            }
        }

        return (new CustomResponseResource([
            'title'   => 'Complaint Updated Successfully',
            'message' => 'The complaint has been updated.',
            'code'    => 200,
            'status'  => 'success',
            'data'    => $complaint,
        ]))->response()->setStatusCode(200);
    }

    public function maintenanceSchedule(Building $building, Request $request)
    {
        $end_date   = $request->has('end_date') ? Carbon::parse($request->end_date) : now()->endOfMonth();
        $start_date = $request->has('start_date') ? Carbon::parse($request->start_date) : now()->startOfMonth();

        $complaints = Complaint::where(['building_id'=> $building->id,'complaint_type'=>'preventive_maintenance'])
            ->when($request->filled(['end_date','start_date']), function ($query) use ($start_date,$end_date) {
                $query->whereBetween('due_date', [$start_date, $end_date]);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                if($request->type === 'completed'){
                    $query->where('status','closed');
                }
                elseif($request->type === 'delayed'){
                    $query->where('due_date','<',now())->where('status','open');
                }
                else{
                    $query;
                }
            })
            ->get();

        return VendorComplaintsResource::collection($complaints);
    }
}
