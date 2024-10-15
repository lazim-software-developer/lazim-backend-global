<?php

namespace App\Http\Controllers\Forms;

use App\Filament\Resources\FitOutFormsDocumentResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContractorFormRequest;
use App\Http\Requests\Forms\CreateFitOutFormsRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\FitOutFormResource;
use App\Jobs\FitOutContractorMailJob;
use App\Models\AccountCredentials;
use App\Models\Building\Building;
use App\Models\Building\Document;
use App\Models\FitOutFormContractorRequest;
use App\Models\Forms\FitOutForm;
use App\Models\Master\DocumentLibrary;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use App\Traits\UtilsTrait;
use Filament\Facades\Filament;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FitOutFormsController extends Controller
{
    use UtilsTrait;
    /**
     * Show the form for creating a new resource.
     */
    public function store(CreateFitOutFormsRequest $request)
    {
        $ownerAssociationId = Building::find($request->building_id)->owner_association_id;

        $form = FitOutForm::create([
            'building_id'                  => $request->building_id,
            'flat_id'                      => $request->flat_id,
            'contractor_name'              => $request->contractor_name,
            'phone'                        => $request->phone,
            'email'                        => $request->email,
            'user_id'                      => auth()->user()->id,
            'undertaking_of_waterproofing' => $request->undertaking_of_waterproofing,
            'no_objection'                 => $request->no_objection,
            'owner_association_id'         => $ownerAssociationId,
            'ticket_number'                => generate_ticket_number("FO"),
        ]);

        $name             = $request->contractor_name;
        $email            = $request->email;
        $tenant           = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id ?? $ownerAssociationId;
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
        FitOutContractorMailJob::dispatch($name, $email, $form, $mailCredentials);

        return (new CustomResponseResource([
            'title'   => 'Success',
            'message' => 'Fit-out created successfully!',
            'code'    => 201,
        ]))->response()->setStatusCode(201);
    }

    public function index(FitOutForm $fitout)
    {

        if ($fitout->status == 'rejected') {
            $rejectedFields = json_decode($fitout->rejected_fields)->rejected_fields;

            $allColumns = Schema::getColumnListing($fitout->getTable());

            // Filter out the rejected fields
            $selectedColumns = array_diff($allColumns, $rejectedFields);

            // Query the MoveInOut model, selecting only the filtered columns
            $fitoutData = FitOutForm::select($selectedColumns)->where('id', $fitout->id)->first();

            $fitoutData->rejected_fields = $rejectedFields;

            return $fitoutData;
        }
        return "Request is not rejected";
    }

    public function contractorRequest(ContractorFormRequest $request, FitOutForm $fitout)
    {

        if ($fitout->contractorRequest) {
            return (new CustomResponseResource([
                'title'   => 'Request already exists!',
                'message' => 'Request already exists for this FitOut form!',
                'code'    => 403,
            ]))->response()->setStatusCode(403);
        }

        $contractor = FitOutFormContractorRequest::create([
            'work_type'       => $request->work_type,
            'work_name'       => $request->work_name,
            'fit_out_form_id' => $fitout->id,
            'status'          => 'submitted',
        ]);
        foreach ($request->documents as $key => $value) {
            $path = optimizeDocumentAndUpload($value);
            $request->merge([
                'name'                 => $key,
                'documentable_id'      => $contractor->id,
                'status'               => 'submitted',
                'documentable_type'    => FitOutFormContractorRequest::class,
                'document_library_id'  => DocumentLibrary::where('name', 'Other documents')->value('id'),
                'url'                  => $path,
                'owner_association_id' => $fitout->owner_association_id,
            ]);
            Document::create($request->all());
        }
        $requiredPermissions = ['view_any_fit::out::forms::document'];
        $roles               = Role::where('owner_association_id', $fitout->owner_association_id)->whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor', 'Staff'])->pluck('id');
        $user                = User::where('owner_association_id', $fitout->owner_association_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
            ->filter(function ($notifyTo) use ($requiredPermissions) {
                return $notifyTo->can($requiredPermissions);
            }); //->where('role_id',Role::where('name','OA')->first()->id)->first();
        Notification::make()
            ->success()
            ->title("Fitout Contractor Request! ")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('New Contractor Fitout Request ')
            ->actions([
                Action::make('view')
                    ->button()
                    ->url(fn () => FitOutFormsDocumentResource::getUrl('edit', [OwnerAssociation::where('id', $fitout->owner_association_id)->first()?->slug, $fitout->id])),
            ])
            ->sendToDatabase($user);

        return (new CustomResponseResource([
            'title'   => 'Successful!',
            'message' => "Contractor request submitted successfully",
            'code'    => 201,
            'status'  => 'success',
        ]))->response()->setStatusCode(201);
    }

    public function verifyContractorRequest(FitOutForm $fitout){
        if ($fitout->contractorRequest) {
            return (new CustomResponseResource([
                'title'   => 'Request already exists!',
                'message' => 'Request already exists for this FitOut form!',
                'code'    => 403,
            ]))->response()->setStatusCode(403);
        }

        return response()->noContent();
    }
     public function fmlist(Vendor $vendor,Request $request)
    {
        $ownerAssociationIds = DB::table('owner_association_vendor')
            ->where('vendor_id',$vendor->id)->pluck('owner_association_id');

        $buildingIds = DB::table('building_owner_association')
                ->whereIn('owner_association_id',$ownerAssociationIds)->pluck('building_id');

        $fitOut = FitOutForm::whereIn('building_id',$buildingIds);

        return FitOutFormResource::collection($fitOut->paginate(10));

    }
     public function updateStatus(Vendor $vendor, FitOutForm $fitOutForm, Request $request)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'remarks' => 'required_if:status,rejected|max:150',
        ]);

        $data = $request->only(['status','remarks']);
        $fitOutForm->update($data);

        return FitOutFormResource::make($fitOutForm);
    }
    public function show(Vendor $vendor, FitOutForm $fitOutForm, Request $request)
    {
        return FitOutFormResource::make($fitOutForm);
    }
}
