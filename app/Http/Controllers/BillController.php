<?php

namespace App\Http\Controllers;

use App\Http\Resources\BillResource;
use App\Models\Bill;
use App\Models\Building\Flat;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function index(Flat $flat)
    {
        $bills = Bill::where('flat_id',$flat->id);

        return BillResource::collection($bills->paginate(10));
    }
}
