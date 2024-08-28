<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicalListRequest;
use App\Http\Requests\VehicleRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\Flat;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function store(VehicleRequest $request){
        $oa_id = Flat::find($request->flat_id)->owner_association_id;
        $request->merge([
            'user_id' => auth()->user()->id,
            'owner_association_id' => $oa_id,
        ]);
        $vehicle = Vehicle::create($request->all());
        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Vehicle added successfully!',
            'status' => 'success',
            'code' => 201,
            'data' => $vehicle,
        ]))->response()->setStatusCode(201);
    }

    public function index(VehicalListRequest $request){
        $vehicles = Vehicle::where(['user_id'=>auth()->user()->id,'flat_id'=>$request->flat_id])->get();
        return $vehicles;
    }
}
