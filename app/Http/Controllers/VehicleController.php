<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function store(VehicleRequest $request){
        $request->merge([
            'user_id' => auth()->user()->id,
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

    public function index(Request $request){
        $vehicles = Vehicle::where('user_id', auth()->user()->id)->get();
        return $vehicles;
    }
}
