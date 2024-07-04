<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\CreateGuestRequest;
use App\Http\Requests\Forms\FlatVisitorRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Forms\VisitorResource;
use App\Jobs\FlatVisitorMailJob;
use App\Jobs\Forms\GuestRequestJob;
use App\Models\Building\Building;
use App\Models\Building\BuildingPoc;
use App\Models\Building\Document;
use App\Models\Building\Flat;
use App\Models\Building\FlatTenant;
use App\Models\ExpoPushNotification;
use App\Models\Forms\Guest;
use App\Models\Master\DocumentLibrary;
use App\Models\Master\Role;
use App\Models\User\User;
use App\Models\Visitor;
use App\Models\Visitor\FlatVisitor;
use App\Traits\UtilsTrait;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuestController extends Controller
{
    use UtilsTrait;
    /**
     * Show the form for creating a new resource.
     */
    public function store(CreateGuestRequest $request)
    {
        $ownerAssociationId = Building::find($request->building_id)->owner_association_id;

        $request->merge([
            'start_time' => $request->start_date,
            'end_time' => $request->end_date,
            'initiated_by' => auth()->user()->id,
            'name' => auth()->user()->first_name,
            'phone' => auth()->user()->phone,
            'email' => auth()->user()->email,
            'owner_association_id' => $ownerAssociationId,
            'ticket_number' => generate_ticket_number("FV")
        ]);
        $guest = FlatVisitor::create($request->all());
        GuestRequestJob::dispatch(auth()->user(), $guest);

        $filePath = optimizeDocumentAndUpload($request->file('image'), 'dev');
        $request->merge([
            'flat_visitor_id' => $guest->id,
            'dtmc_license_url' => $filePath,
            'passport_number' =>json_encode($request->visitor_passports),
            'guest_name' => json_encode($request->visitor_names),
        ]);
        Guest::create($request->all());

        // Handle multiple images
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $image) {
                $filePath = optimizeDocumentAndUpload($image, 'dev');
                $currentDate = date('Y-m-d');

                //TODO: NEED TO CHANGE EXPIRY_DATE LOGIC
                $passportId = DocumentLibrary::where('name', 'Passport')->value('id');

                $request->merge([
                    'documentable_id' => $guest->id,
                    'document_library_id' => $passportId,
                    'status' => 'pending',
                    'url' => $filePath,
                    'expiry_date' => date('Y-m-d', strtotime('+1 year', strtotime($currentDate))),
                    'documentable_type' => FlatVisitor::class,
                    'name' => $request->type,
                ]);

                Document::create($request->all());
            }
        }
        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => ' created successfully!',
            'code' => 201,
        ]))->response()->setStatusCode(201);
    }

    public function saveFlatVisitors(FlatVisitorRequest $request)
    {
        $ownerAssociationId = DB::table('building_owner_association')->where('building_id', $request->building_id)->where('active', true)->first()?->owner_association_id;
        // $ownerAssociationId = Building::find($request->building_id)->owner_association_id;

        $request->merge([
            'start_time' => $request->start_date,
            'end_time' => $request->start_date,
            'phone' => "NA",
            'email' => $request->email,
            'owner_association_id' => $ownerAssociationId,
            'type' => 'visitor'
        ]);

        $requiredPermissions = ['view_any_visitor::form'];
        $visitor = FlatVisitor::create($request->all());
        $roles = Role::where('owner_association_id',$ownerAssociationId)->whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff'])->pluck('id');
        $user = User::where('owner_association_id', $ownerAssociationId)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()//->where('role_id', Role::where('name','OA')->value('id'))->get();
        ->filter(function ($notifyTo) use ($requiredPermissions) {
            return $notifyTo->can($requiredPermissions);
        });
        Notification::make()
            ->success()
            ->title('Flat Visit Request')
            ->body("Flat visit request received for $request->start_date")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->sendToDatabase($user);

        // Handle multiple images
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $image) {
                $filePath = optimizeDocumentAndUpload($image, 'dev');
                $currentDate = date('Y-m-d');

                $emiratesId = DocumentLibrary::where('name', 'Eid')->value('id');

                $request->merge([
                    'documentable_id' => $visitor->id,
                    'document_library_id' => $emiratesId,
                    'status' => 'pending',
                    'url' => $filePath,
                    'expiry_date' => date('Y-m-d', strtotime('+1 year', strtotime($currentDate))),
                    'documentable_type' => FlatVisitor::class,
                    'name' => 'Visitor document',
                ]);

                Document::create($request->all());
            }
        }

        $code = generateAlphanumericOTP();
        $visitor->update([
            'verification_code' => $code,
        ]);
        FlatVisitorMailJob::dispatch($visitor,$code);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => ' created successfully!',
            'code' => 201,
        ]))->response()->setStatusCode(201);
    }

    public function visitorRequest(Request $request){
        $visitor = FlatVisitor::where('verification_code', $request->code)->first();
        abort_if(!$visitor,403,'Invalid verification code');

        if ($visitor->status == null){
            return [
                'data' =>[
                'visitor_id' => $visitor->id,
                'visitor_name' => $visitor->name,
                'visitor_email' => $visitor->email,
                'number_of_visitors' => $visitor->number_of_visitors,
                'visiting_time' => $visitor->time_of_viewing,
                'status' => $visitor->status
                ]
            ];
        }
        else{
            return (new CustomResponseResource([
                'title' => 'Status already updated',
                'message' => 'Status of this visitor is already updated.',
                'code' => 403,
                'data' => [
                    'visitor_id' => $visitor->id,
                    'visitor_name' => $visitor->name,
                    'visitor_email' => $visitor->email,
                    'number_of_visitors' => $visitor->number_of_visitors,
                    'visiting_time' => $visitor->time_of_viewing,
                    'status' => $visitor->status
                    ]
            ]))->response()->setStatusCode(403);
        }
    }
    public function visitorApproval(Request $request, FlatVisitor $visitor){
        $visitor->update([
            'approved_by' => auth()->user()?->id,
            'status' => $request->status
        ]); 
    }

    // List all future visits for a building
    public function futureVisits(Building $building)
    {
        // List only approved requests from flat_visitors table
        $futureVisits = FlatVisitor::where('building_id', $building->id)
            ->where('start_time', '>', now())
            ->where('type', 'visitor')
            ->orderBy('start_time')
            ->get();

        return VisitorResource::collection($futureVisits);
    }

    // Notify tenant on visitor's visit
    public function notifyTenant(Request $request) {
        $flat = $request->input('flat_id');
        $building = $request->input('building_id');

        $user = FlatTenant::where(['flat_id' => $flat, 'building_id' => $building, 'active' => 1])->first();

        if($user) {
            Visitor::create($request->all());

            $expoPushTokens = ExpoPushNotification::where('user_id', $user->tenant_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {

                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Visitors',
                        'body' => "You have a visitor as $request->type \n name: $request->name",
                        'data' => ['notificationType' => 'VisitorAllowReject'],
                    ];
                    $this->expoNotification($message);
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $user->tenant_id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => "You have a visitor as $request->type \n name: $request->name",
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Visitors',
                            'view' => 'notifications::notification',
                            'viewData' => [],
                            'format' => 'filament',
                            'url' => 'VisitorAllowReject',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
                }
                }

            // TODO:Notify user
            return (new CustomResponseResource([
                'title' => 'Success',
                'message' => ' created successfully!',
                'code' => 201,
            ]))->response()->setStatusCode(201);
        }

        return (new CustomResponseResource([
            'title' => 'Error',
            'message' => 'No active tenant present in this unit!',
            'code' => 400,
        ]))->response()->setStatusCode(400);

    }

    public function visitorEntry(Request $request)
    {
        $notification = DB::table('notifications')->find($request->notification_id);
        $flatTenant= FlatTenant::where('tenant_id', $notification->notifiable_id)->where('active', true)->first();
        $visitor= Visitor::where('building_id',$flatTenant->building_id)->where('flat_id',$flatTenant->flat_id)->latest()->first();
        $visitor->update([
            "status" => $request->status,
            "approved_by" => auth()->user()->id,
        ]);
        DB::table('notifications')->where('id', $request->notification_id)->update(['read_at' => now()]);

        if ($request->status == "approved"){
            $security= BuildingPoc::where('building_id',$flatTenant->building_id)->where('active',true)->first()->user_id;
                $expoPushTokens = ExpoPushNotification::where('user_id', $security)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                $unit= Flat::find($flatTenant->flat_id)->property_number;
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Visitors',
                        'body' => "Allow Visitors of flat $unit",
                        'data' => ['notificationType' => 'InAppNotfication'],
                    ];
                    $this->expoNotification($message);
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $security,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => "Allow Visitors of flat $unit ",
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Visitors',
                            'view' => 'notifications::notification',
                            'viewData' => [],
                            'format' => 'filament',
                            'url' => '',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
                }
            }
        }
        if($request->status == "rejected"){
            $security= BuildingPoc::where('building_id',$flatTenant->building_id)->where('active',true)->first()->user_id;
                $expoPushTokens = ExpoPushNotification::where('user_id', $security)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                $unit= Flat::find($flatTenant->flat_id)->property_number;
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Visitors',
                        'body' => "Don't allow Visitors of flat $unit",
                        'data' => ['notificationType' => 'InAppNotfication'],
                    ];
                    $this->expoNotification($message);
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $security,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => "Don't allow Visitors of flat $unit ",
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Visitors',
                            'view' => 'notifications::notification',
                            'viewData' => [],
                            'format' => 'filament',
                            'url' => '',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
                }
        }
    }
    return (new CustomResponseResource([
        'title' => 'Success',
        'message' => 'successfull!',
        'code' => 200,
    ]))->response()->setStatusCode(200);
}
}
