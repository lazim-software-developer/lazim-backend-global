<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomResponseResource;
use App\Jobs\EnquiryMailJob;
use App\Models\Enquiry;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function store(Request $request){
        
        $enquiry = Enquiry::create($request->all());

        EnquiryMailJob::dispatch($enquiry);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Enquiry submitted successfully!',
            'code' => 201,
            'status' => 'success',
            'data' => $enquiry,
        ]))->response()->setStatusCode(201);
    }
}
