<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\CreateAccessCardFormsRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\Forms\AccessCard;
use Carbon\Carbon;

class AccessCardController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(CreateAccessCardFormsRequest $request)
    {
        // Handle multiple images
        $document_paths = [
            'tenancy',
            'vehicle_registration',
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

        AccessCard::create($data);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Access card submitted successfully!',
            'errorCode' => 201,
        ]))->response()->setStatusCode(201);
    }

    public function fetchFormStatus() {

        // Fetch status of all forms
        $accessCard = auth()->user()->accessCard()->latest()->first();

        $accessCardStatus = $accessCard ?? "Not submitted";

        $residentialForm = auth()->user()->residentialForm()->latest()->first();

        $residentialFormStatus = $residentialForm ?? "Not submitted";

        $fitOutForm = auth()->user()->fitOut()->latest()->first();

        $fitOutFormStatus = $fitOutForm ?? "Not submitted";
        
        $moveInForm = auth()->user()->moveinData()->where('type', 'movein')->latest()->first();

        $moveInFormStatus = $moveInForm ?? "Not submitted";
        
        $moveOutForm = auth()->user()->moveinData()->where('type', 'moveout')->latest()->first();

        $moveOutFormStatus = $moveOutForm ?? "Not submitted";
        
        $saleNocForm = auth()->user()->saleNoc()->latest()->first();

        $saleNocFormStatus = $saleNocForm ?? "Not submitted";

       return $forms = [
            [
                'name' => 'Access Card',
                'status' => $accessCard ? $accessCard->status : 'Not submitted',
                'created_at' => $accessCard ? Carbon::parse($accessCard->created_at)->diffForHumans() : null,
                'rejected_reason' => $accessCard ? $accessCard->remarks : null
            ],
            [
                'name' => 'Residential Form',
                'status' => $residentialForm ? $residentialForm->status : 'Not submitted',
                'created_at' => $residentialForm ? Carbon::parse($residentialForm->created_at)->diffForHumans() : null,
                'rejected_reason' => $residentialForm ? $residentialForm->remarks : null
            ],
            [
                'name' => 'Fit Out Form',
                'status' => $fitOutForm ? $fitOutForm->status : 'Not submitted',
                'created_at' => $fitOutForm ? Carbon::parse($fitOutForm->created_at)->diffForHumans() : null,
                'rejected_reason' => $fitOutForm ? $fitOutForm->remarks : null
            ],
            [
                'name' => 'Move In Form',
                'status' => $moveInForm ? $moveInForm->status : 'Not submitted',
                'created_at' => $moveInForm ? Carbon::parse($moveInForm->created_at)->diffForHumans() : null,
                'rejected_reason' => $moveInForm ? $moveInForm->remarks : null
            ],
            [
                'name' => 'Move Out Form',
                'status' => $moveOutForm ? $moveOutForm->status : 'Not submitted',
                'created_at' => $moveOutForm ? Carbon::parse($moveOutForm->created_at)->diffForHumans() : null,
                'rejected_reason' => $moveOutForm ? $moveOutForm->remarks : null
            ],
            [
                'name' => 'Sale NOC Form',
                'status' => $saleNocForm ? $saleNocForm->status : 'Not submitted',
                'created_at' => $saleNocForm ? Carbon::parse($saleNocForm->created_at)->diffForHumans() : null,
                'rejected_reason' => $saleNocForm ? $saleNocForm->remarks : null
            ]
        ];
    }
}
