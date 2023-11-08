<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\SaleNocRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\Building;
use App\Models\Forms\NocContacts;
use App\Models\Forms\NocFormSignedDocument;
use App\Models\Forms\SaleNOC;
use Illuminate\Http\Request;

class SaleNocController extends Controller
{
    public function store(SaleNocRequest $request)
    {
        // Upload files using the fucntion optimizeDocumentAndUpload
        $validated = $request->validated();

        $ownerAssociationId = Building::find($request->building_id)->owner_association_id;

        // Handle the file uploads using the optimizeDocumentAndUpload function
        $validated['cooling_receipt'] = optimizeDocumentAndUpload($request->file('cooling_receipt'));
        $validated['cooling_soa'] = optimizeDocumentAndUpload($request->file('cooling_soa'));
        $validated['cooling_clearance'] = optimizeDocumentAndUpload($request->file('cooling_clearance'));
        $validated['payment_receipt'] = optimizeDocumentAndUpload($request->file('payment_receipt'));


        $validated['user_id'] = auth()->user()->id;
        $validated['owner_association_id'] = $ownerAssociationId;
        $validated['submit_status'] = 'download_file';

        // Create the SaleNoc entry
        $saleNoc = SaleNoc::create($validated);

        $contacts = $request->get('contacts');

        foreach ($contacts as $index => $contact) {
            // Handle file uploads for emirates_document_url
            if ($request->hasFile("contacts.$index.emirates_document_url")) {
                $file = optimizeDocumentAndUpload($request->file("contacts.$index.emirates_document_url"));
                $contact['emirates_document_url'] = $file;
            }

            // Handle file uploads for visa_document_url
            if ($request->hasFile("contacts.$index.visa_document_url")) {
                $file = optimizeDocumentAndUpload($request->file("contacts.$index.visa_document_url"));
                $contact['visa_document_url'] = $file;
            }

            // Handle file uploads for passport_document_url
            if ($request->hasFile("contacts.$index.passport_document_url")) {
                $file = optimizeDocumentAndUpload($request->file("contacts.$index.passport_document_url"));
                $contact['passport_document_url'] = $file;
            }

            $contact['noc_form_id'] = $saleNoc->id;
            $contact['first_name'] = auth()->user()->first_name;
            $contact['last_name'] = auth()->user()->last_name;
            $contact['email'] = auth()->user()->email;
            $contact['mobile'] = auth()->user()->phone;
            NocContacts::create($contact);
        }

        return response()->json([
            'message' => 'SaleNoc created successfully!',
        ], 201);
    }

    // Fetch NOC for status using id
    public function fetchNocFormStatus(SaleNOC $saleNoc)
    {
        $status = $saleNoc->submit_status;

        if ($status == 'download_file') {
            return response()->json([
                'message' => 'download_file',
                'link' => env('APP_URL') . 'service-charge/' . $saleNoc->id . '/generate-pdf'
            ], 200);
        } else if ($status == 'seller_uploaded') {
            return response()->json([
                'message' => 'buyer_uploaded',
                'link' => ""
            ], 200);
        } else if ($status == 'buyer_uploaded') {
            return response()->json([
                'message' => '',
                'link' => ""
            ], 200);
        }
    }

    // Upload Signed document from buyer or seller
    public function uploadDocument(Request $request, SaleNOC $saleNoc)
    {
        $filePath = optimizeDocumentAndUpload($request->file, 'dev');

        // Check the existing value of submit_status column
        $status = $saleNoc->submit_status;

        if ($status == 'download_file') {
            $saleNoc->update(['submit_status' => 'seller_uploaded']);

            // Upload document to NocFormSignedDocument
            NocFormSignedDocument::create([
                'noc_form_id' => $saleNoc->id,
                'document' => $filePath,
                'uploaded_by' => auth()->user()->id
            ]);
        } else if ($status == 'seller_uploaded') {
            $saleNoc->update(['submit_status' => 'buyer_uploaded']);

            // Upload document to NocFormSignedDocument
            NocFormSignedDocument::create([
                'noc_form_id' => $saleNoc->id,
                'document' => $filePath,
                'uploaded_by' => auth()->user()->id
            ]);
        }

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'document uploaded successfully',
            'errorCode' => 200,
            'status' => 'success'
        ]))->response()->setStatusCode(200);
    }
}
