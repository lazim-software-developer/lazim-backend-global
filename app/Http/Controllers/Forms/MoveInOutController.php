<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\CreateFormRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\Building;
use App\Models\ExpoPushNotification;
use App\Models\Forms\MoveInOut;
use App\Traits\UtilsTrait;
use Illuminate\Support\Facades\Schema;

class MoveInOutController extends Controller
{
    use UtilsTrait;
    public function index(MoveInOut $movein)
    {
        if ($movein->status == 'rejected') {
            $rejectedFields = json_decode($movein->rejected_fields);

            $allColumns = Schema::getColumnListing($movein->getTable());

            // Filter out the rejected fields
            $selectedColumns = array_diff($allColumns, $rejectedFields->rejected_fields);

            // Query the MoveInOut model, selecting only the filtered columns
            $moveInOutData = MoveInOut::select($selectedColumns)->get();

            $moveInOutData->rejected_fields = json_decode($movein->rejected_fields);

            return $moveInOutData;
        }
        return "Request is not rejected";
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(CreateFormRequest $request)
    {
        $ownerAssociationId = Building::find($request->building_id)->owner_association_id;

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


        $data = $request->all();  // Get all request data

        foreach ($document_paths as $document) {
            if ($request->hasFile($document)) {
                $file = $request->file($document);
                $data[$document] = optimizeDocumentAndUpload($file, 'dev');
            }
        }

        $data['name'] = auth()->user()->first_name;
        $data['phone']= auth()->user()->phone;
        $data['email']= auth()->user()->email;
        $data['user_id']= auth()->user()->id;
        $data['owner_association_id']= $ownerAssociationId;

        MoveInOut::create($data);
        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Form submitted successfully!',
            'code' => 201,
            'status' => 'success',
        ]))->response()->setStatusCode(201);
    }
}
