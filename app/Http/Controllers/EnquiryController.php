<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomResponseResource;
use App\Jobs\EnquiryMailJob;
use App\Models\Enquiry;
use App\Models\OwnerAssociation;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function store(Request $request)
    {

        $enquiry = Enquiry::create($request->all());

        $tenant           = Filament::getTenant()?->id ?? auth()->user()->owner_association_id;
        $emailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()->email ?? env('MAIL_FROM_ADDRESS');

        EnquiryMailJob::dispatch($enquiry, $emailCredentials);

        return (new CustomResponseResource([
            'title'   => 'Success',
            'message' => 'Enquiry submitted successfully!',
            'code'    => 201,
            'status'  => 'success',
            'data'    => $enquiry,
        ]))->response()->setStatusCode(201);
    }
}
