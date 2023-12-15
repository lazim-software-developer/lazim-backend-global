<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\SaleNocRequest;
use App\Http\Resources\CustomResponseResource;
use App\Jobs\SendSaleNocEmail;
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

        $validated['user_id'] = auth()->user()->id;
        $validated['owner_association_id'] = $ownerAssociationId;
        $validated['submit_status'] = 'download_file';

        // Create the SaleNoc entry
        $saleNoc = SaleNoc::create($validated);

        $contacts = $request->get('contacts');

        foreach ($contacts as $index => $contact) {
           
            $contact['noc_form_id'] = $saleNoc->id;

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
            $document = NocFormSignedDocument::create([
                'noc_form_id' => $saleNoc->id,
                'document' => $filePath,
                'uploaded_by' => auth()->user()->id
            ]);

            // Send email to buyers attaching the document
            SendSaleNocEmail::dispatch($saleNoc, $document)->delay(5);

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
            'code' => 200,
            'status' => 'success'
        ]))->response()->setStatusCode(200);
    }

    // Upload individual documents for NOC form
    public function uploadNOCDocument(Request $request) {
        $path = optimizeDocumentAndUpload($request->file, 'dev');

        return response()->json([
            'path' => $path,
        ], 200);
    }
}
