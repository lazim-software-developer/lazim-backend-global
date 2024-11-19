<?php

namespace App\Http\Controllers;

use App\Models\RentalDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Resources\RentalDetailsResource;

class RentalDetailsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'flat_id' => 'required|exists:flats,id',
        ]);

        $rentalDetails = RentalDetail::where('flat_id',$request->flat_id);

        if ($request->filled('date')) {
            $date = Carbon::createFromFormat('m-Y', $request->date);
            $rentalDetails->whereHas('rentalCheques',function($query) use ($date){
                $query->whereMonth('due_date', $date->month)->whereYear('due_date', $date->year);
            });
        }

        return RentalDetailsResource::collection($rentalDetails->paginate(10));
    }
}
