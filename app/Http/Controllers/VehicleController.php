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
    public function store(VehicleRequest $request)
    {
        $flat = Flat::find($request->flat_id);
        $oa_id = $flat?->owner_association_id;

        $vehicleCount = Vehicle::where('flat_id',$request->flat_id)->get()->count();

        if ($vehicleCount > $flat->parking_count) {
            return (new CustomResponseResource([
                'title' => 'No Slots!',
                'message' => "No Available parking slot for this flat.",
                'code' => 403,
                'type' => 'error'
            ]))->response()->setStatusCode(403);
        }

        $parking_number = $flat?->property_number . '-' . $request->parking_number;
        if (Vehicle::where('parking_number', $parking_number)->exists()){
            return (new CustomResponseResource([
                'title' => 'parking number exists',
                'message' => "parking number you have entered is already being used.",
                'code' => 403,
                'type' => 'error'
            ]))->response()->setStatusCode(403);
        }
        $request->offsetSet('parking_number', $parking_number);
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

    public function index(VehicalListRequest $request)
    {
        $vehicles = Vehicle::where(['user_id' => auth()->user()->id, 'flat_id' => $request->flat_id])->get();
        return $vehicles;
    }
}
