<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\SaleNocRequest;
use App\Models\Forms\NocContacts;
use App\Models\Forms\SaleNOC;

class SaleNocController extends Controller
{
    public function store(SaleNocRequest $request)
    {
        // Upload files using the fucntion optimizeDocumentAndUpload
        $validated = $request->validated();

        // Handle the file uploads using the optimizeDocumentAndUpload function
        $validated['cooling_receipt'] = optimizeDocumentAndUpload($request->file('cooling_receipt'));
        $validated['cooling_soa'] = optimizeDocumentAndUpload($request->file('cooling_soa'));
        $validated['cooling_clearance'] = optimizeDocumentAndUpload($request->file('cooling_clearance'));
        $validated['payment_receipt'] = optimizeDocumentAndUpload($request->file('payment_receipt'));


        $validated['user_id'] = auth()->user()->id;
        $validated['status'] = 'submitted';

        // Create the SaleNoc entry
        $saleNoc = SaleNoc::create($validated);

        $contacts = $request->get('contacts');

        foreach ($contacts as $index => $contact) {
            // Handle file uploads for emirates_document_url
            if ($request->hasFile("contacts.$index.emirates_document_url")) {
                $file = optimizeDocumentAndUpload($request->file("contacts.$index.emirates_document_url"))                ;
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

            NocContacts::create($contact);
        }

        return response()->json([
            'message' => 'SaleNoc created successfully!',
        ], 201);
    }
}
