<?php

namespace App\Http\Controllers;

use App\Http\Resources\RentalDetailsResource;
use App\Models\RentalDetail;
use Illuminate\Http\Request;

class RentalDetailsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'flat_id' => 'required|exists:flats,id',
        ]);

        $rentalDetails = RentalDetail::where('flat_id',$request->flat_id);

        return RentalDetailsResource::collection($rentalDetails->paginate(10));
    }
}
