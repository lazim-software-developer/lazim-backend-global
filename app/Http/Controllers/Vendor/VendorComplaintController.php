<?php

namespace App\Http\Controllers\Vendor;

use App\Models\Building\FlatTenant;
use Carbon\Carbon;
use App\Models\Media;
use App\Traits\UtilsTrait;
use Illuminate\Http\Request;
use App\Models\Vendor\Vendor;
use App\Models\Master\Service;
use App\Models\TechnicianVendor;
use App\Models\Community\Comment;
use App\Models\AccountCredentials;
use App\Models\Building\Complaint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Building\BuildingPoc;
use App\Models\ExpoPushNotification;
use App\Jobs\Complaint\ComplaintCreationJob;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Community\CommentResource;
use App\Http\Requests\VendorComplaintCreateRequest;
use App\Http\Requests\Community\StoreCommentRequest;
use App\Http\Resources\Vendor\VendorComplaintsResource;

class VendorComplaintController extends Controller
{
    use UtilsTrait;
    public function listComplaints(Request $request, Vendor $vendor)
    {
        $end_date   = now();
        $start_date = now()->subDays(7);
        if ($request->duration == 'lastMonth') {
            $start_date = now()->subDays(30);
        };
        if ($request->duration == 'custom') {
            $end_date   = $request->end_date;
            $start_date = $request->start_date;
        }
        $complaints = Complaint::where('vendor_id', $vendor->id)
            ->when($request->filled('building_id'), function ($query) use ($request) {
                $query->where('building_id', $request->building_id);
            })
            ->when($request->filled('type'), function ($query) use ($vendor, $request) {
                $buildings = $vendor->buildings->where('pivot.active', true)->where('pivot.end_date', '>', now()->toDateString())->unique()
                                ->filter(function($buildings) use($request){
                                        return $buildings->ownerAssociations->contains('role',$request->type);
                                });

                $query->whereIn('building_id', $buildings->pluck('id'));
            })
            ->when($request->filled('complaint_type'), function ($query) use ($request) {
                $query->where('complaint_type', $request->complaint_type);
            }, function ($query) {
                // Default to 'help_desk' and 'tenant_complaint' if complaint_type is not sent
                $query->whereIn('complaint_type', ['help_desk', 'tenant_complaint', 'snag', 'oa_complaint_report']);
            })
            ->whereBetween('updated_at', [$start_date, $end_date])
            ->latest()->paginate($request->paginate ?? 10);

        return VendorComplaintsResource::collection($complaints);
    }

    public function addComment(StoreCommentRequest $request, Complaint $complaint)
    {
        $comment = new Comment([
            'body'    => $request->body,
            'user_id' => auth()->user()->id,
        ]);

        $complaint->comments()->save($comment);

        return (new CustomResponseResource([
            'title'     => 'Success',
            'message'   => "Comment added successfully",
            'errorCode' => 201,
            'status'    => 'success',
            'data'      => new CommentResource($comment),
        ]))->response()->setStatusCode(201);
    }

    public function create(Vendor $vendor, VendorComplaintCreateRequest $request)
    {
        $categoryName = $request->category ? Service::where('id', $request->category)->value('name') : '';

        $service_id = $request->category ?? null;

        $owner_association_id = DB::connection('mysql')->table('building_owner_association')
            ->where(['building_id' => $request->building_id, 'active' => true])
            ->first()?->owner_association_id;

        $request->merge([
            'complaintable_type'   => Vendor::class,
            'complaintable_id'     => $vendor->id,
            'user_id'              => $vendor->owner_id,
            'category'             => $categoryName,
            'selected_service'     => $request->has('selected_service') ? $request->selected_service : null,
            'open_time'            => now(),
            'technician_id'        => TechnicianVendor::find($request->technician_id)->technician_id,
            'status'               => 'open',
            'building_id'          => $request->building_id,
            'owner_association_id' => $owner_association_id,
            'ticket_number'        => generate_ticket_number("CP"),
        ]);

        // assign a vendor if the complaint type is tenant_complaint or help_desk
        if ($request->complaint_type == 'tenant_complaint' || $request->complaint_type == 'help_desk' || $request->complaint_type == 'snag' || $request->complaint_type == 'preventive_maintenance') {
            $request->merge([
                'priority'   => $request?->urgent != 'false' ? 1 : 3,
                'due_date'   => $request->complaint_type === 'preventive_maintenance' ? $request->due_date : now()->addDays(3),
                'service_id' => $service_id,
                'vendor_id'  => $vendor->id,
                'type'       => $request->type ?: null,
            ]);
        }

        // Create the complaint and assign it the vendor
        // TODO: Assign ticket automatically to technician
        $complaint = Complaint::create($request->all());

        // sending push notification for security
        if ($categoryName == 'Security Services') {

            $isActiveSecurity = BuildingPoc::where([
                'role_name'   => 'security',
                'building_id' => $request->building_id,
                'active'      => 1,
            ])->first();
            if ($isActiveSecurity) {
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

        if ($complaint->technician_id) {

            $credentials     = AccountCredentials::where('oa_id', $complaint->owner_association_id)->where('active', true)->latest()->first();
            $mailCredentials = [
                'mail_host'         => $credentials->host ?? env('MAIL_HOST'),
                'mail_port'         => $credentials->port ?? env('MAIL_PORT'),
                'mail_username'     => $credentials->username ?? env('MAIL_USERNAME'),
                'mail_password'     => $credentials->password ?? env('MAIL_PASSWORD'),
                'mail_encryption'   => $credentials->encryption ?? env('MAIL_ENCRYPTION'),
                'mail_from_address' => $credentials->email ?? env('MAIL_FROM_ADDRESS'),
            ];
            ComplaintCreationJob::dispatch($complaint->id, $complaint->technician_id, $mailCredentials);

            $expoPushToken = ExpoPushNotification::where('user_id', $complaint->technician_id)->first()?->token;
            if ($expoPushToken) {
                $message = [
                    'to'    => $expoPushToken,
                    'sound' => 'default',
                    'title' => $complaint->complaint_type == 'preventive_maintenance' ? 'Schedule Assigned' :'Task Assigned',
                    'body'  => $complaint->complaint_type == 'preventive_maintenance' ? 'Schedule has been assigned' :'Task has been assigned',
                    'data'  => ['notificationType' => 'PendingRequests'],
                ];
                $this->expoNotification($message);
                DB::table('notifications')->insert([
                    'id'              => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'type'            => 'Filament\Notifications\DatabaseNotification',
                    'notifiable_type' => 'App\Models\User\User',
                    'notifiable_id'   => $complaint->technician_id,
                    'data'            => json_encode([
                        'actions'   => [],
                        'body'      => $complaint->complaint_type == 'preventive_maintenance' ? 'Schedule has been assigned' :'Task has been assigned',
                        'duration'  => 'persistent',
                        'icon'      => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'title'     => $complaint->complaint_type == 'preventive_maintenance' ? 'Schedule Assigned' :'Task Assigned',
                        'view'      => 'notifications::notification',
                        'viewData'  => [],
                        'format'    => 'filament',
                        'url'       => 'PendingRequests',
                    ]),
                    'created_at'      => now()->format('Y-m-d H:i:s'),
                    'updated_at'      => now()->format('Y-m-d H:i:s'),
                ]);
            } else {
                Log::info("No technicians to add", []);
            }
        }
        if($complaint->complaint_type === 'preventive_maintenance'){
            $residentIds = FlatTenant::where([
                'building_id' => $complaint->building_id,
                'active' => true
            ])->distinct()->pluck('tenant_id');
            if($residentIds->count() > 0){
                // Create individual notifications for each resident
                foreach($residentIds as $residentId) {
                    $residentTokens = ExpoPushNotification::where('user_id',$residentId)->first()?->token;
                    $message = [
                        'to'    => $residentTokens,
                        'sound' => 'default',
                        'title' => 'Preventive Maintenance',
                        'body'  => 'A preventive maintenance has been scheduled for your building',
                        'data'  => [
                                'notificationType' => 'PreventiveMaintenance',
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
                        'notifiable_id'   => $residentId,
                        'data'            => json_encode([
                            'actions'   => [],
                            'body'      => 'A preventive maintenance has been scheduled for your building',
                            'duration'  => 'persistent',
                            'icon'      => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title'     => 'Preventive Maintenance',
                            'view'      => 'notifications::notification',
                            'viewData'  => [
                                'complaintId'      => $complaint?->id,
                                'open_time' => $complaint?->open_time,
                                'close_time' => $complaint?->close_time,
                                'due_date' => $complaint?->due_date,
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
        return (new CustomResponseResource([
            'title'   => 'Success',
            'message' => "We'll get back to you at the earliest!",
            'code'    => 201,
            'status'  => 'success',
        ]))->response()->setStatusCode(201);
    }
    public function preventiveMaintenance(Vendor $vendor,Request $request)
    {
        $end_date   = $request->has('end_date') ? Carbon::parse($request->end_date) : now();
        $start_date = $request->has('start_date') ? Carbon::parse($request->start_date) : now()->subDays(6);

        $complaints = Complaint::where(['vendor_id'=> $vendor->id,'complaint_type'=>'preventive_maintenance'])
            ->whereIn('building_id', $vendor->buildings->where('pivot.active', true)->unique()->pluck('id'))
            ->when($request->filled('building_id'), function ($query) use ($request) {
                $query->where('building_id', $request->building_id);
            })
            ->when($request->filled(['end_date','start_date']), function ($query) use ($request,$start_date,$end_date) {
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
            ->paginate($request->paginate ?? 10);

        return VendorComplaintsResource::collection($complaints);
    }

    public function dashboardPreventive(Vendor $vendor,Request $request)
    {
        $complaints = Complaint::where(['vendor_id'=> $vendor->id,'complaint_type'=>'preventive_maintenance'])
            ->whereIn('building_id', $vendor->buildings->where('pivot.active', true)->unique()->pluck('id'))
            ->when($request->filled('building_id'), function ($query) use ($request) {
                $query->where('building_id', $request->building_id);
            })->get();

        return [
            'scheduled' => $complaints->count(),
            'completed' => $complaints->where('status','closed')->count(),
            'delayed'   => $complaints->where('due_date','<',now())->where('status','open')->count(),
        ];
    }
    public function dashboardReactive(Vendor $vendor,Request $request)
    {
        $complaints = Complaint::where(['vendor_id'=> $vendor->id])
            ->whereIn('complaint_type', ['tenant_complaint','help_desk','snag'])
            ->when($request->filled('building_id'), function ($query) use ($request) {
                $query->where('building_id', $request->building_id);
            })->get();

        return [
            'ongoing'   => $complaints->where('status','in-progress')->count(),
            'pending'   => $complaints->where('status','open')->count(),
            'completed' => $complaints->where('status','closed')->count(),
        ];
    }
    public function reactiveMaintenance(Vendor $vendor,Request $request)
    {

        $complaints = Complaint::where(['vendor_id'=> $vendor->id])
            ->whereIn('complaint_type', ['tenant_complaint','help_desk','snag'])
            ->when($request->filled('building_id'), function ($query) use ($request) {
                $query->where('building_id', $request->building_id);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                if($request->type === 'completed'){
                    $query->where('status','closed');
                }
                elseif($request->type === 'pending'){
                    $query->where('status','open');
                }
                else{
                    $query->where('status','in-progress');
                }
            })
            ->paginate($request->paginate ?? 10);

        return VendorComplaintsResource::collection($complaints);
    }
}
