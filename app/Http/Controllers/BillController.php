<?php

namespace App\Http\Controllers;

use App\Http\Resources\BillResource;
use App\Models\Bill;
use App\Models\Building\Flat;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BillController extends Controller
{
    public function index(Flat $flat, Request $request)
    {
        $bills = Bill::where('flat_id', $flat->id);

        if ($request->filled('date')) {
            $date = Carbon::createFromFormat('m-Y', $request->date);
            $bills->whereMonth('month', $date->month)
                  ->whereYear('month', $date->year);
        }

        return BillResource::collection($bills->paginate(10));
    }
}
