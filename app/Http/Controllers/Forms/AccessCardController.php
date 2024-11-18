<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\FetchFormStatusRequest;
use App\Http\Requests\Forms\CreateAccessCardFormsRequest;
use App\Http\Resources\AccessCardFormResource;
use App\Http\Resources\CustomResponseResource;
use App\Jobs\Forms\AccessCardRequestJob;
use App\Models\AccountCredentials;
use App\Models\Building\Building;
use App\Models\ExpoPushNotification;
use App\Models\Forms\AccessCard;
use App\Models\Forms\Guest;
use App\Models\Order;
use App\Models\OwnerAssociation;
use App\Models\Vendor\Vendor;
use App\Models\WorkPermit;
use App\Traits\UtilsTrait;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccessCardController extends Controller
{
    use UtilsTrait;
    /**
     * Show the form for creating a new resource.
     */
    public function create(CreateAccessCardFormsRequest $request)
    {
        $ownerAssociationId = DB::table('building_owner_association')->where(['building_id' => $request->building_id,'active'=>true])->first()?->owner_association_id;

        // Handle multiple images
        $document_paths = [
            'tenancy',
            'vehicle_registration',
            'title_deed',
            'passport',
        ];

        $data = $request->all();
        foreach ($document_paths as $document) {
            if ($request->has($document)) {
                $file            = $request->file($document);
                $data[$document] = optimizeDocumentAndUpload($file, 'dev');
            }
        }

        $data['user_id']              = auth()->user()->id;
        $data['mobile']               = auth()->user()->phone;
        $data['email']                = auth()->user()->email;
        $data['owner_association_id'] = $ownerAssociationId;
        $data['ticket_number']        = generate_ticket_number("AC");

        $accessCard       = AccessCard::create($data);
        $tenant           = Filament::getTenant()?->id ?? $ownerAssociationId;
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
        AccessCardRequestJob::dispatch(auth()->user(), $accessCard, $mailCredentials);

        return (new CustomResponseResource([
            'title'   => 'Success',
            'message' => 'Access card submitted successfully!',
            'code'    => 201,
        ]))->response()->setStatusCode(201);
    }

    public function fetchFormStatus(Building $building, FetchFormStatusRequest $request)
    {
        $flat_id = $request->input('flat_id');
        // Fetch status of all forms
        $accessCard = auth()->user()->accessCard()->where('building_id', $building->id)->where('flat_id',$flat_id)->latest()->first();

        $accessCardStatus = $accessCard ?? "Not submitted";

        $residentialForm = auth()->user()->residentialForm()->where('building_id', $building->id)->where('flat_id',$flat_id)->latest()->first();

        $residentialFormStatus = $residentialForm ?? "Not submitted";

        $fitOutForm = auth()->user()->fitOut()->latest()->where('building_id', $building->id)->where('flat_id',$flat_id)->first();

        $fitOutFormStatus = $fitOutForm ?? "Not submitted";

        $permitToWork = auth()->user()->bookings()->where('bookable_type', WorkPermit::class)->where('building_id', $building->id)->where('flat_id',$flat_id)->latest()->first();

        $moveInForm = auth()->user()->moveinData()->where('type', 'move-in')->where('building_id', $building->id)->where('flat_id',$flat_id)->latest()->first();

        $moveInFormStatus = $moveInForm ?? "Not submitted";

        $moveOutForm = auth()->user()->moveinData()->where('type', 'move-out')->where('building_id', $building->id)->where('flat_id',$flat_id)->latest()->first();

        $moveOutFormStatus = $moveOutForm ?? "Not submitted";

        $saleNocForm = auth()->user()->saleNoc()->latest()->where('building_id', $building->id)->where('flat_id',$flat_id)->first();

        $saleNocFormStatus = $saleNocForm ?? "Not submitted";

        $guestRegistration = auth()->user()->flatVisitorInitates()->where('type', 'guest')->latest()->where('building_id', $building->id)->where('flat_id',$flat_id)->first();

        $guest = Guest::where('flat_visitor_id', $guestRegistration?->id)->latest()->first();

        $guestRegistrationStatus = $guestRegistration ?? "Not submitted";

        $nocMessage = null;

        if ($saleNocFormStatus !== "Not submitted" && $saleNocForm->submit_status === 'seller_uploaded') {
            $nocMessage = "Upload buyer's signed copy";
        } else if ($saleNocFormStatus !== "Not submitted" && $saleNocForm->submit_status === 'download_file') {
            $nocMessage = 'Download the file and upload signed copy';
        }

        return $forms = [
            [
                'id'              => $accessCard ? $accessCard->id : null,
                'name'            => 'Access Card',
                'status'          => $accessCard ? $accessCard->status : 'not_submitted',
                'created_at'      => $accessCard ? Carbon::parse($accessCard->created_at)->diffForHumans() : null,
                'rejected_reason' => $accessCard ? $accessCard->remarks : null,
                'message'         => null,
                'payment_link'    => $accessCard?->payment_link,
                'order_id'        => $accessCard?->orders[0]->id ?? null,
                'order_status'    => $accessCard?->orders[0]->payment_status ?? null,
            ],
            [
                'id'              => $residentialForm ? $residentialForm->id : null,
                'name'            => 'Residential Form',
                'status'          => $residentialForm ? $residentialForm->status : 'not_submitted',
                'created_at'      => $residentialForm ? Carbon::parse($residentialForm->created_at)->diffForHumans() : null,
                'rejected_reason' => $residentialForm ? $residentialForm->remarks : null,
                'message'         => null,
                'payment_link'    => null,
                'order_id'        => null,
                'order_status'    => null,
            ],
            [
                'id'              => $fitOutForm ? $fitOutForm->id : null,
                'name'            => 'Fitout Form',
                'status'          => $fitOutForm ? $fitOutForm->status : 'not_submitted',
                'created_at'      => $fitOutForm ? Carbon::parse($fitOutForm->created_at)->diffForHumans() : null,
                'rejected_reason' => $fitOutForm ? $fitOutForm->remarks : null,
                'message'         => null,
                'payment_link'    => $fitOutForm?->payment_link,
                'order_id'        => $fitOutForm?->orders[0]->id ?? null,
                'order_status'    => $fitOutForm?->orders[0]->payment_status ?? null,
            ],
            [
                'id'              => $moveInForm ? $moveInForm->id : null,
                'name'            => 'Move In Form',
                'status'          => $moveInForm ? $moveInForm->status : 'not_submitted',
                'created_at'      => $moveInForm ? Carbon::parse($moveInForm->created_at)->diffForHumans() : null,
                'rejected_reason' => $moveInForm ? $moveInForm->remarks : null,
                'message'         => null,
                'payment_link'    => null,
                'order_id'        => null,
                'order_status'    => null,
            ],
            [
                'id'              => $moveOutForm ? $moveOutForm->id : null,
                'name'            => 'Move Out Form',
                'status'          => $moveOutForm ? $moveOutForm->status : 'not_submitted',
                'created_at'      => $moveOutForm ? Carbon::parse($moveOutForm->created_at)->diffForHumans() : null,
                'rejected_reason' => $moveOutForm ? $moveOutForm->remarks : null,
                'message'         => null,
                'payment_link'    => null,
                'order_id'        => null,
                'order_status'    => null,
            ],
            [
                'id'              => $saleNocForm ? $saleNocForm->id : null,
                'name'            => 'Sale NOC Form',
                'status'          => $saleNocForm ? $saleNocForm->status : 'not_submitted',
                'created_at'      => $saleNocForm ? Carbon::parse($saleNocForm->created_at)->diffForHumans() : null,
                'rejected_reason' => $saleNocForm ? $saleNocForm->remarks : null,
                'message'         => $nocMessage,
                'payment_link'    => $saleNocForm?->payment_link,
                'order_id'        => $saleNocForm?->orders[0]->id ?? null,
                'order_status'    => $saleNocForm?->orders[0]->payment_status ?? 'pending',
            ],
            [
                'id'              => $guestRegistration ? $guestRegistration->id : null,
                'name'            => 'Holiday Homes Guest Registration Form',
                'status'          => $guest ? $guest->status : 'not_submitted',
                'created_at'      => $guestRegistration ? Carbon::parse($guestRegistration->created_at)->diffForHumans() : null,
                'rejected_reason' => $guest ? $guest->remarks : null,
                'message'         => null,
                'payment_link'    => null,
                'order_id'        => null,
                'order_status'    => null,
            ],
            [
                'id'              => $permitToWork ? $permitToWork->id : null,
                'name'            => 'Permit To Work Form',
                'status'          => $permitToWork ? $permitToWork->approved : 'not_submitted',
                'created_at'      => $permitToWork ? Carbon::parse($permitToWork->created_at)->diffForHumans() : null,
                'rejected_reason' => $permitToWork ? $permitToWork->remarks : null,
                'message'         => null,
                'payment_link'    => null,
                'order_id'        => null,
                'order_status'    => null,
            ],
        ];
    }
    public function fmlist(Vendor $vendor)
    {
        $ownerAssociationIds = DB::table('owner_association_vendor')
            ->where('vendor_id', $vendor->id)->pluck('owner_association_id');

        $buildingIds = DB::table('building_owner_association')
            ->whereIn('owner_association_id', $ownerAssociationIds)->pluck('building_id');

        $accessCardForms = AccessCard::whereIn('building_id', $buildingIds)->orderByDesc('created_at');

        return AccessCardFormResource::collection($accessCardForms->paginate(10));
    }
     public function updateStatus(Vendor $vendor, AccessCard $accessCard, Request $request)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'remarks' => 'required_if:status,rejected|max:150',
        ]);
        $data = $request->only(['status', 'remarks']);
        $accessCard->update($data);

        if ($request->status == 'approved') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $accessCard->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to'    => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Access card form status',
                        'body'  => 'Your access card form has been approved.',
                        'data'  => ['notificationType' => 'MyRequest'],
                    ];

                    $this->expoNotification($message);
                }
            }

            DB::table('notifications')->insert([
                'id'              => (string) \Ramsey\Uuid\Uuid::uuid4(),
                'type'            => 'Filament\Notifications\DatabaseNotification',
                'notifiable_type' => 'App\Models\User\User',
                'notifiable_id'   => $accessCard->user_id,
                'data'            => json_encode([
                    'actions'   => [],
                    'body'      => 'Your access card form has been approved. ',
                    'duration'  => 'persistent',
                    'icon'      => 'heroicon-o-document-text',
                    'iconColor' => 'warning',
                    'title'     => 'Access card form status',
                    'view'      => 'notifications::notification',
                    'viewData'  => [],
                    'format'    => 'filament',
                    'url'       => 'MyRequest',
                ]),
                'created_at'      => now()->format('Y-m-d H:i:s'),
                'updated_at'      => now()->format('Y-m-d H:i:s'),
            ]);
            // Generate payment link and save it in access_cards_table

            try {
                $payment = createPaymentIntent(env('ACCESS_CARD_AMOUNT'), 'punithprachi113@gmail.com');

                if ($payment) {
                    $accessCard->update([
                        'payment_link' => $payment->client_secret,
                    ]);

                    // Create an entry in orders table with status pending
                    Order::create([
                        'orderable_id'      => $accessCard->id,
                        'orderable_type'    => AccessCard::class,
                        'payment_status'    => 'pending',
                        'amount'            => env('ACCESS_CARD_AMOUNT'),
                        'payment_intent_id' => $payment->id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
        if ($request->status == 'rejected') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $accessCard->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to'    => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Access card form status!',
                        'body'  => 'Your access card form has been rejected.',
                        'data'  => ['notificationType' => 'MyRequest'],
                    ];
                    $this->expoNotification($message);
                }
            }
            DB::table('notifications')->insert([
                'id'              => (string) \Ramsey\Uuid\Uuid::uuid4(),
                'type'            => 'Filament\Notifications\DatabaseNotification',
                'notifiable_type' => 'App\Models\User\User',
                'notifiable_id'   => $accessCard->user_id,
                'data'            => json_encode([
                    'actions'   => [],
                    'body'      => 'Your access card form has been rejected.',
                    'duration'  => 'persistent',
                    'icon'      => 'heroicon-o-document-text',
                    'iconColor' => 'danger',
                    'title'     => 'Access card form status!',
                    'view'      => 'notifications::notification',
                    'viewData'  => [],
                    'format'    => 'filament',
                    'url'       => 'MyRequest',
                ]),
                'created_at'      => now()->format('Y-m-d H:i:s'),
                'updated_at'      => now()->format('Y-m-d H:i:s'),
            ]);
        }

        return AccessCardFormResource::make($accessCard);
    }

    public function show(Vendor $vendor, AccessCard $accessCard, Request $request)
    {
        return AccessCardFormResource::make($accessCard);
    }
}
