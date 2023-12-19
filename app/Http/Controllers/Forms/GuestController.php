<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\CreateGuestRequest;
use App\Http\Requests\Forms\FlatVisitorRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Forms\VisitorResource;
use App\Models\Building\Building;
use App\Models\Building\Document;
use App\Models\Building\FlatTenant;
use App\Models\ExpoPushNotification;
use App\Models\Forms\Guest;
use App\Models\Master\DocumentLibrary;
use App\Models\Master\Role;
use App\Models\User\User;
use App\Models\Visitor;
use App\Models\Visitor\FlatVisitor;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuestController extends Controller
{
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
            'owner_association_id' => $ownerAssociationId
        ]);
        $guest = FlatVisitor::create($request->all());

        $filePath = optimizeDocumentAndUpload($request->file('image'), 'dev');
        $request->merge([
            'flat_visitor_id' => $guest->id,
            'dtmc_license_url' => $filePath,
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
        $ownerAssociationId = Building::find($request->building_id)->owner_association_id;

        $request->merge([
            'start_time' => $request->start_date,
            'end_time' => $request->start_date,
            'phone' => "NA",
            'email' => $request->email,
            'owner_association_id' => $ownerAssociationId,
            'type' => 'visitor'
        ]);

        $visitor = FlatVisitor::create($request->all());
        $user = User::where('owner_association_id', $ownerAssociationId)->where('role_id', Role::where('name','OA')->value('id'))->get();
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
        
        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => ' created successfully!',
            'code' => 201,
        ]))->response()->setStatusCode(201);
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
                        'body' => "You have a visitor to visit",
                        'data' => ['notificationType' => 'MyRequest'],
                    ];
                    $this->expoNotification($message);
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $user->tenant_id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => "You have a visitor to visit ",
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Visitors',
                            'view' => 'notifications::notification',
                            'viewData' => [],
                            'format' => 'filament'
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
}
