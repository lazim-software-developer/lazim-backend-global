<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\CreateFitOutFormsRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\Building;
use App\Models\Forms\FitOutForm;
use Illuminate\Support\Facades\Schema;

class FitOutFormsController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function store(CreateFitOutFormsRequest $request)
    {
        $ownerAssociationId = Building::find($request->building_id)->owner_association_id;

        FitOutForm::create([
            'building_id' => $request->building_id,
            'flat_id' => $request->flat_id,
            'contractor_name' => $request->contractor_name,
            'phone'=> $request->phone,
            'email' =>$request->email,
            'user_id'=> auth()->user()->id,
            'undertaking_of_waterproofing'=>$request->undertaking_of_waterproofing,
            'no_objection'=>$request->no_objection,
            'owner_association_id' => $ownerAssociationId
        ]);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Fit-out created successfully!',
            'code' => 201,
        ]))->response()->setStatusCode(201);
    }

    public function index(FitOutForm $fitout){
        
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
}
