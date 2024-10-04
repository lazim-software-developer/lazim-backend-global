<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\ResidentialFormRequest;
use App\Http\Resources\ResidentialFormResource;
use App\Jobs\Forms\ResidentialFormRequestJob;
use App\Models\AccountCredentials;
use App\Models\Building\Building;
use App\Models\ExpoPushNotification;
use App\Models\OwnerAssociation;
use App\Models\ResidentialForm;
use App\Models\Vendor\Vendor;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ResidentialFormController extends Controller
{
    public function store(ResidentialFormRequest $request)
    {
        $validated = $request->validated();

        $ownerAssociationId = Building::find($request->building_id)->owner_association_id;

        $validated['passport_url'] = optimizeDocumentAndUpload($request->file('file_passport_url'));
        $validated['emirates_url'] = optimizeDocumentAndUpload($request->file('file_emirates_url'));

        if ($request->hasFile('file_title_deed_url')) {
            $validated['title_deed_url'] = optimizeDocumentAndUpload($request->file('file_title_deed_url'));
        }
        if ($request->hasFile('file_tenancy_contract')) {
            $validated['tenancy_contract'] = optimizeDocumentAndUpload($request->file('file_tenancy_contract'));
        }

        $validated['user_id']              = auth()->user()->id;
        $validated['owner_association_id'] = $ownerAssociationId;
        $validated['ticket_number']        = generate_ticket_number("FV");

        $residentialForm  = ResidentialForm::create($validated);
        $tenant           = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id ?? $ownerAssociationId;
        // $emailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');
        $credentials = AccountCredentials::where('oa_id', $tenant)->where('active', true)->latest()->first();
        $mailCredentials = [
            'mail_host' => $credentials->host ?? env('MAIL_HOST'),
            'mail_port' => $credentials->port ?? env('MAIL_PORT'),
            'mail_username' => $credentials->username ?? env('MAIL_USERNAME'),
            'mail_password' => $credentials->password ?? env('MAIL_PASSWORD'),
            'mail_encryption' => $credentials->encryption ?? env('MAIL_ENCRYPTION'),
            'mail_from_address' => $credentials->email ?? env('MAIL_FROM_ADDRESS'),
        ];
        ResidentialFormRequestJob::dispatch(auth()->user(), $residentialForm, $mailCredentials);

        return response()->json([
            'message' => 'Form successfully created',
            'data'    => $residentialForm,
        ], 201);
    }
    public function fmlist(Vendor $vendor)
    {
        $ownerAssociationIds = DB::table('owner_association_vendor')
            ->where('vendor_id', $vendor->id)->pluck('owner_association_id');

        $buildingIds = DB::table('building_owner_association')
            ->whereIn('owner_association_id', $ownerAssociationIds)->pluck('building_id');

        $residentForms = ResidentialForm::whereIn('building_id', $buildingIds);

        return ResidentialFormResource::collection($residentForms->paginate(10));
    }
    public function updateStatus(Vendor $vendor, ResidentialForm $residentialForm, Request $request)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'remarks' => 'required_if:status,rejected|max:150',
        ]);
        $data = $request->only(['status', 'remarks']);
        $residentialForm->update($data);

        if ($request->status == 'approved') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $residentialForm->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to'    => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Residential form status',
                        'body'  => 'Your residential form has been approved.',
                        'data'  => ['notificationType' => 'MyRequest'],
                    ];
                    $this->expoNotification($message);
                }
            }
            DB::table('notifications')->insert([
                'id'              => (string) \Ramsey\Uuid\Uuid::uuid4(),
                'type'            => 'Filament\Notifications\DatabaseNotification',
                'notifiable_type' => 'App\Models\User\User',
                'notifiable_id'   => $residentialForm->user_id,
                'data'            => json_encode([
                    'actions'   => [],
                    'body'      => 'Your residential form has been approved.',
                    'duration'  => 'persistent',
                    'icon'      => 'heroicon-o-document-text',
                    'iconColor' => 'warning',
                    'title'     => 'Residential form status',
                    'view'      => 'notifications::notification',
                    'viewData'  => [],
                    'format'    => 'filament',
                    'url'       => 'MyRequest',
                ]),
                'created_at'      => now()->format('Y-m-d H:i:s'),
                'updated_at'      => now()->format('Y-m-d H:i:s'),
            ]);
        }

        if ($request->status == 'rejected') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $residentialForm->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to'    => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Residential form status',
                        'body'  => 'Your residential form has been rejected.',
                        'data'  => ['notificationType' => 'MyRequest'],
                    ];
                    $this->expoNotification($message);
                }
            }
            DB::table('notifications')->insert([
                'id'              => (string) \Ramsey\Uuid\Uuid::uuid4(),
                'type'            => 'Filament\Notifications\DatabaseNotification',
                'notifiable_type' => 'App\Models\User\User',
                'notifiable_id'   => $residentialForm->user_id,
                'data'            => json_encode([
                    'actions'   => [],
                    'body'      => 'Your residential form has been rejected.',
                    'duration'  => 'persistent',
                    'icon'      => 'heroicon-o-document-text',
                    'iconColor' => 'danger',
                    'title'     => 'Residential form status',
                    'view'      => 'notifications::notification',
                    'viewData'  => [],
                    'format'    => 'filament',
                    'url'       => 'MyRequest',
                ]),
                'created_at'      => now()->format('Y-m-d H:i:s'),
                'updated_at'      => now()->format('Y-m-d H:i:s'),
            ]);
        }


        return ResidentialFormResource::make($residentialForm);
    }
}
