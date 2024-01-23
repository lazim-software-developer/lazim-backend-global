<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\CreateAccessCardFormsRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\Building;
use App\Models\Forms\AccessCard;
use Carbon\Carbon;

class AccessCardController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(CreateAccessCardFormsRequest $request)
    {
        $ownerAssociationId = Building::find($request->building_id)->owner_association_id;
        
        // Handle multiple images
        $document_paths = [
            'tenancy',
            'vehicle_registration',
            'title_deed',
            'passport'
        ];
        
        $data = $request->all();
        foreach ($document_paths as $document) {
            if($request->has($document)) {
                $file = $request->file($document);
                $data[$document] = optimizeDocumentAndUpload($file, 'dev');
            }
        }

        $data['user_id'] = auth()->user()->id;
        $data['mobile']= auth()->user()->phone;
        $data['email'] = auth()->user()->email;
        $data['owner_association_id'] = $ownerAssociationId;

        AccessCard::create($data);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Access card submitted successfully!',
            'code' => 201,
        ]))->response()->setStatusCode(201);
    }

    public function fetchFormStatus(Building $building) 
    {
        // Fetch status of all forms
        $accessCard = auth()->user()->accessCard()->where('building_id', $building->id)->latest()->first();

        $accessCardStatus = $accessCard ?? "Not submitted";

        $residentialForm = auth()->user()->residentialForm()->where('building_id', $building->id)->latest()->first();

        $residentialFormStatus = $residentialForm ?? "Not submitted";

        $fitOutForm = auth()->user()->fitOut()->latest()->where('building_id', $building->id)->first();

        $fitOutFormStatus = $fitOutForm ?? "Not submitted";
        
        $moveInForm = auth()->user()->moveinData()->where('type', 'move-in')->where('building_id', $building->id)->latest()->first();

        $moveInFormStatus = $moveInForm ?? "Not submitted";
        
        $moveOutForm = auth()->user()->moveinData()->where('type', 'move-out')->where('building_id', $building->id)->latest()->first();

        $moveOutFormStatus = $moveOutForm ?? "Not submitted";
        
        $saleNocForm = auth()->user()->saleNoc()->latest()->where('building_id', $building->id)->first();

        $saleNocFormStatus = $saleNocForm ?? "Not submitted";

        $nocMessage = null;

        if ($saleNocFormStatus !== "Not submitted" && $saleNocForm->submit_status === 'seller_uploaded') {
            $nocMessage = "Upload buyer's signed copy";
        } else if ($saleNocFormStatus !== "Not submitted" && $saleNocForm->submit_status === 'download_file') {
            $nocMessage = 'Download the file and upload signed copy';
        }

        return $forms = [
            [
                'id' => $accessCard ? $accessCard->id : null,
                'name' => 'Access Card',
                'status' => $accessCard ? $accessCard->status : 'not_submitted',
                'created_at' => $accessCard ? Carbon::parse($accessCard->created_at)->diffForHumans() : null,
                'rejected_reason' => $accessCard ? $accessCard->remarks : null,
                'message' => null,
                'payment_link' => $accessCard?->payment_link,
                'order_id' => $accessCard?->orders[0]->id ?? null,
                'order_status' => $accessCard?->orders[0]->payment_status ?? null,
            ],
            [
                'id' => $residentialForm ? $residentialForm->id : null,
                'name' => 'Residential Form',
                'status' => $residentialForm ? $residentialForm->status : 'not_submitted',
                'created_at' => $residentialForm ? Carbon::parse($residentialForm->created_at)->diffForHumans() : null,
                'rejected_reason' => $residentialForm ? $residentialForm->remarks : null,
                'message' => null,
                'payment_link' => null,
                'order_id' => null,
                'order_status' => null
            ],
            [
                'id' => $fitOutForm ? $fitOutForm->id : null,
                'name' => 'Fit Out Form',
                'status' => $fitOutForm ? $fitOutForm->status : 'not_submitted',
                'created_at' => $fitOutForm ? Carbon::parse($fitOutForm->created_at)->diffForHumans() : null,
                'rejected_reason' => $fitOutForm ? $fitOutForm->remarks : null,
                'message' => null,
                'payment_link' => null,
                'order_id' => null,
                'order_status' => null
            ],
            [
                'id' => $moveInForm ? $moveInForm->id : null,
                'name' => 'Move In Form',
                'status' => $moveInForm ? $moveInForm->status : 'not_submitted',
                'created_at' => $moveInForm ? Carbon::parse($moveInForm->created_at)->diffForHumans() : null,
                'rejected_reason' => $moveInForm ? $moveInForm->remarks : null,
                'message' => null,
                'payment_link' => null,
                'order_id' => null,
                'order_status' => null
            ],
            [
                'id' => $moveOutForm ? $moveOutForm->id : null,
                'name' => 'Move Out Form',
                'status' => $moveOutForm ? $moveOutForm->status : 'not_submitted',
                'created_at' => $moveOutForm ? Carbon::parse($moveOutForm->created_at)->diffForHumans() : null,
                'rejected_reason' => $moveOutForm ? $moveOutForm->remarks : null,
                'message' => null,
                'payment_link' => null,
                'order_id' => null,
                'order_status' => null
            ],
            [
                'id' => $saleNocForm ? $saleNocForm->id : null,
                'name' => 'Sale NOC Form',
                'status' => $saleNocForm ? $saleNocForm->status : 'not_submitted',
                'created_at' => $saleNocForm ? Carbon::parse($saleNocForm->created_at)->diffForHumans() : null,
                'rejected_reason' => $saleNocForm ? $saleNocForm->remarks : null,
                'message' => $nocMessage,
                'payment_link' => $saleNocForm?->payment_link,
                'order_id' => $saleNocForm?->orders[0]->id ?? null,
                'order_status' => $saleNocForm?->orders[0]->payment_status ?? 'pending',
            ]
        ];
    }
}
