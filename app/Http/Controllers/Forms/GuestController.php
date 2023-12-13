<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\CreateGuestRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\Building;
use App\Models\Building\Document;
use App\Models\Forms\Guest;
use App\Models\Master\DocumentLibrary;
use App\Models\Visitor\FlatVisitor;

class GuestController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function store(CreateGuestRequest $request)
    {
        $ownerAssociationId = Building::find($request->building_id)->owner_association_id;

        $request->merge([
            'start_time' => $request->start_date,
            'end_time' => $request->end_date,
            'initiated_by' => auth()->user()->id,
            'name' => auth()->user()->first_name,
            'phone' => auth()->user()->phone,
            'email' => auth()->user()->email,
            'owner_association_id' => $ownerAssociationId
        ]);
        $guest = FlatVisitor::create($request->all());

        $filePath = optimizeDocumentAndUpload($request->file('image'), 'dev');
        $request->merge([
            'flat_visitor_id'=> $guest->id,
            'dtmc_license_url'=> $filePath,
        ]);
        Guest::create($request->all());

        // Handle multiple images
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $image) {
                $filePath = optimizeDocumentAndUpload($image, 'dev');
                $currentDate = date('Y-m-d');

                //TODO: NEED TO CHANGE EXPIRY_DATE LOGIC
                $passportId = DocumentLibrary::where('name', 'Passport')->value('id');

                $request->merge([
                    'documentable_id' => $guest->id,
                    'document_library_id' => $passportId,
                    'status' => 'pending',
                    'url' => $filePath,
                    'expiry_date' => date('Y-m-d', strtotime('+1 year', strtotime($currentDate))),
                    'documentable_type' => FlatVisitor::class,
                    'name' => $request->type,
                ]);

                Document::create($request->all());
            }
        }
        return (new CustomResponseResource([
                'title' => 'Success',
                'message' => ' created successfully!',
                'code' => 201,
            ]))->response()->setStatusCode(201);
    }
}
