<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\CreateFormRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\MoveInOutResource;
use App\Jobs\MoveInOutMailJob;
use App\Models\AccountCredentials;
use App\Models\Building\Building;
use App\Models\ExpoPushNotification;
use App\Models\Forms\MoveInOut;
use App\Models\OwnerAssociation;
use App\Models\Vendor\Vendor;
use App\Traits\UtilsTrait;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MoveInOutController extends Controller
{
    use UtilsTrait;
    public function index(MoveInOut $movein)
    {
        if ($movein->status == 'rejected') {
            $rejectedFields = json_decode($movein->rejected_fields)->rejected_fields;

            $allColumns = Schema::getColumnListing($movein->getTable());

            // Filter out the rejected fields
            $selectedColumns = array_diff($allColumns, $rejectedFields);

            // Query the MoveInOut model, selecting only the filtered columns
            $moveInOutData = MoveInOut::select($selectedColumns)->where('id', $movein->id)->first();

            $moveInOutData->rejected_fields = $rejectedFields;

            return $moveInOutData;
        }
        return "Request is not rejected";
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(CreateFormRequest $request)
    {
        $ownerAssociationId = DB::table('building_owner_association')->where(['building_id' => $request->building_id,'active'=>true])->first()?->owner_association_id;

        $tenant           = Filament::getTenant()?->id ?? $ownerAssociationId;
        // $emailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()->email ?? env('MAIL_FROM_ADDRESS');
        $credentials = AccountCredentials::where('oa_id', $tenant)->where('active', true)->latest()->first();
        $mailCredentials = [
            'mail_host' => $credentials->host ?? env('MAIL_HOST'),
            'mail_port' => $credentials->port ?? env('MAIL_PORT'),
            'mail_username' => $credentials->username ?? env('MAIL_USERNAME'),
            'mail_password' => $credentials->password ?? env('MAIL_PASSWORD'),
            'mail_encryption' => $credentials->encryption ?? env('MAIL_ENCRYPTION'),
            'mail_from_address' => $credentials->email ?? env('MAIL_FROM_ADDRESS'),
        ];
        // Handle multiple images
        $document_paths = [
            'handover_acceptance',
            'receipt_charges',
            'contract',
            'title_deed',
            'passport',
            'dewa',
            'cooling_registration',
            'gas_registration',
            'vehicle_registration',
            'movers_license',
            'movers_liability',
            'etisalat_final',
            'dewa_final',
            'gas_clearance',
            'cooling_clearance',
            'gas_final',
            'cooling_final',
            'noc_landlord',
        ];

        $data = $request->all();

        foreach ($document_paths as $document) {
            if ($request->hasFile($document)) {
                $file            = $request->file($document);
                $data[$document] = optimizeDocumentAndUpload($file, 'dev');
            }
        }

        $data['name']                 = auth()->user()->first_name;
        $data['phone']                = auth()->user()->phone;
        $data['email']                = auth()->user()->email;
        $data['user_id']              = auth()->user()->id;
        $data['owner_association_id'] = $ownerAssociationId;
        $data['ticket_number']        = generate_ticket_number("MV");

        $moveInOut = MoveInOut::create($data);
        MoveInOutMailJob::dispatch(auth()->user(), $moveInOut, $mailCredentials);

        return (new CustomResponseResource([
            'title'   => 'Success',
            'message' => 'Form submitted successfully!',
            'code'    => 201,
            'status'  => 'success',
        ]))->response()->setStatusCode(201);
    }

    // Update details for move in and move out
    public function update(Request $request, MoveInOut $movein)
    {
        $document_paths = [
            'handover_acceptance',
            'receipt_charges',
            'contract',
            'title_deed',
            'passport',
            'dewa',
            'cooling_registration',
            'gas_registration',
            'vehicle_registration',
            'movers_license',
            'movers_liability',
            'etisalat_final',
            'dewa_final',
            'gas_clearance',
            'cooling_clearance',
            'gas_final',
            'cooling_final',
            'noc_landlord',
        ];

        $data = $request->all();

        foreach ($document_paths as $document) {
            if ($request->hasFile($document)) {
                $file            = $request->file($document);
                $data[$document] = optimizeDocumentAndUpload($file, 'dev');
            }
        }

        $movein->update($data);

        return (new CustomResponseResource([
            'title'   => 'Success',
            'message' => 'Form edited successfully!',
            'code'    => 200,
            'status'  => 'success',
        ]))->response()->setStatusCode(200);
    }

    public function list(Request $request)
    {
        $request->validate([
            'building_id' => 'required',
        ]);
        $mov = MoveInOut::where('status', 'approved')->where('moving_date', '>=', now()->toDateString())->where('building_id', $request->building_id);
        if ($request->filled('type')) {
            $mov->where('type', $request->type);
        }

        $mov = $mov->orderBy('moving_date')->get();
        return MoveInOutResource::collection($mov);
    }

    public function fmlist(Vendor $vendor,Request $request)
    {
        $request->validate([
            'type' => 'required|in:move-out,move-in',
        ]);

        $ownerAssociationIds = DB::table('owner_association_vendor')
            ->where('vendor_id',$vendor->id)->pluck('owner_association_id');

        $buildingIds = DB::table('building_owner_association')
                ->whereIn('owner_association_id',$ownerAssociationIds)->pluck('building_id');

        $moveInOut = MoveInOut::whereIn('building_id',$buildingIds)->where('type',$request->type)->orderByDesc('created_at');

        return MoveInOutResource::collection($moveInOut->paginate(10));

    }
    public function updateStatus(Vendor $vendor, MoveInOut $moveInOut, Request $request)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'remarks' => 'required_if:status,rejected|max:150',
        ]);
        $data = $request->only(['status', 'remarks']);
        $moveInOut->update($data);

        if ($request->status == 'approved') {

            //notification for who is created the form
            $expoPushTokens = ExpoPushNotification::where('user_id', $moveInOut->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to'    => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Move-in form status',
                        'body'  => 'Your move-in form has been approved.',
                        'data'  => ['notificationType' => 'MyRequest'],
                    ];
                    $this->expoNotification($message);
                }
            }
            DB::table('notifications')->insert([
                'id'              => (string) \Ramsey\Uuid\Uuid::uuid4(),
                'type'            => 'Filament\Notifications\DatabaseNotification',
                'notifiable_type' => 'App\Models\User\User',
                'notifiable_id'   => $moveInOut->user_id,
                'data'            => json_encode([
                    'actions'   => [],
                    'body'      => 'Your move-in form has been approved.',
                    'duration'  => 'persistent',
                    'icon'      => 'heroicon-o-document-text',
                    'iconColor' => 'warning',
                    'title'     => 'Move-in form status',
                    'view'      => 'notifications::notification',
                    'viewData'  => [],
                    'format'    => 'filament',
                    'url'       => 'MyRequest',
                ]),
                'created_at'      => now()->format('Y-m-d H:i:s'),
                'updated_at'      => now()->format('Y-m-d H:i:s'),
            ]);
            $security = $moveInOut->building->buildingPocs->where('active', true)->where('role_name', 'security')->first();
            if ($security?->exists()) {
                $id             = $security?->first()?->user_id;
                $expoPushTokens = ExpoPushNotification::where('user_id', $id)->pluck('token');
                if ($expoPushTokens->count() > 0) {
                    foreach ($expoPushTokens as $expoPushToken) {
                        $message = [
                            'to'    => $expoPushToken,
                            'sound' => 'default',
                            'title' => 'Move-in',
                            'body'  => 'New move-in form received.',
                            'data'  => ['notificationType' => 'Move-in'],
                        ];
                        $this->expoNotification($message);
                    }
                }
                DB::table('notifications')->insert([
                    'id'              => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'type'            => 'Filament\Notifications\DatabaseNotification',
                    'notifiable_type' => 'App\Models\User\User',
                    'notifiable_id'   => $id,
                    'data'            => json_encode([
                        'actions'   => [],
                        'body'      => 'New move-in form received.',
                        'duration'  => 'persistent',
                        'icon'      => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'title'     => 'Move-in',
                        'view'      => 'notifications::notification',
                        'viewData'  => [],
                        'format'    => 'filament',
                        'url'       => 'Move-in',
                    ]),
                    'created_at'      => now()->format('Y-m-d H:i:s'),
                    'updated_at'      => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }

        if ($request->status == 'rejected') {
            //notification to whoever create submit the form
            $expoPushTokens = ExpoPushNotification::where('user_id', $moveInOut->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to'    => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Move-in form status',
                        'body'  => 'Your move-in form has been rejected.',
                        'data'  => ['notificationType' => 'MyRequest'],
                    ];
                    $this->expoNotification($message);
                }
            }
            DB::table('notifications')->insert([
                'id'              => (string) \Ramsey\Uuid\Uuid::uuid4(),
                'type'            => 'Filament\Notifications\DatabaseNotification',
                'notifiable_type' => 'App\Models\User\User',
                'notifiable_id'   => $moveInOut->user_id,
                'data'            => json_encode([
                    'actions'   => [],
                    'body'      => 'Your move-in form has been rejected.',
                    'duration'  => 'persistent',
                    'icon'      => 'heroicon-o-document-text',
                    'iconColor' => 'danger',
                    'title'     => 'Move-in form status',
                    'view'      => 'notifications::notification',
                    'viewData'  => [],
                    'format'    => 'filament',
                    'url'       => 'MyRequest',
                ]),
                'created_at'      => now()->format('Y-m-d H:i:s'),
                'updated_at'      => now()->format('Y-m-d H:i:s'),
            ]);
        }


        return MoveInOutResource::make($moveInOut);
    }
    public function show(Vendor $vendor, MoveInOut $moveInOut, Request $request)
    {
        return MoveInOutResource::make($moveInOut);
    }
}
