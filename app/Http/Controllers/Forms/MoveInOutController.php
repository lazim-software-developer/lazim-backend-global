<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\CreateFormRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\MoveInOutResource;
use App\Jobs\MoveInOutMailJob;
use App\Models\Building\Building;
use App\Models\ExpoPushNotification;
use App\Models\Forms\MoveInOut;
use App\Traits\UtilsTrait;
use Illuminate\Http\Request;
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


        $data = $request->all();

        foreach ($document_paths as $document) {
            if ($request->hasFile($document)) {
                $file = $request->file($document);
                $data[$document] = optimizeDocumentAndUpload($file, 'dev');
            }
        }

        $data['name'] = auth()->user()->first_name;
        $data['phone'] = auth()->user()->phone;
        $data['email'] = auth()->user()->email;
        $data['user_id'] = auth()->user()->id;
        $data['owner_association_id'] = $ownerAssociationId;
        $data['ticket_number'] = "MV" . date("i") . "-" . strtoupper(bin2hex(random_bytes(2))) . "-" . date("md");

        $moveInOut = MoveInOut::create($data);
        MoveInOutMailJob::dispatch(auth()->user(), $moveInOut);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Form submitted successfully!',
            'code' => 201,
            'status' => 'success',
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
                $file = $request->file($document);
                $data[$document] = optimizeDocumentAndUpload($file, 'dev');
            }
        }

        $movein->update($data);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Form edited successfully!',
            'code' => 200,
            'status' => 'success',
        ]))->response()->setStatusCode(200);
    }

    public function list(Request $request){
        $request->validate([
            'building_id' => 'required'
        ]);
        $mov = MoveInOut::where('status','approved')->where('moving_date','>=',now()->toDateString())->where('building_id',$request->building_id)->orderBy('moving_date')->get();
        return MoveInOutResource::collection($mov);
    }
}
