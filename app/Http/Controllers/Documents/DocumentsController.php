<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Document\DocumentRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Documents\DocumentLibraryResource;
use App\Http\Resources\Documents\DocumentResource;
use App\Models\Building\Document;
use App\Models\Master\DocumentLibrary;
use App\Models\Media;
use App\Models\User\User;

class DocumentsController extends Controller
{
    public function index()
    {
        $documents = DocumentLibrary::where('label', 'master')->get();
        return DocumentLibraryResource::collection($documents);
    }

    public function create(DocumentRequest $request)
    {
        $currentDate = date('Y-m-d');
        $document = Document::create([
            'document_library_id' => $request->document_library_id,
            'building_id' => $request->building_id,
            'documentable_id' => auth()->user()->id,
            'status' => 'submitted',
            'expiry_date' => date('Y-m-d', strtotime('+1 year', strtotime($currentDate))), //to do need to make changes for expiry date
            'documentable_type' => User::class,
            'name' => $request->name,
            'flat_id' => $request->flat ?? null
        ]);

        // Handle multiple images
        if ($request->file('file')) {
            $filePath = optimizeDocumentAndUpload($request->file, 'dev');

            // Create a new media entry for image
            Media::create([
                'name' => basename($filePath), // Extracts filename from the full path
                'url' => $filePath,
                'mediaable_id' => $document->id,
                'mediaable_type' => Document::class
            ]);

            $document->url = $filePath;
            $document->save();

            return new CustomResponseResource([
                'title' => 'Document Submitted',
                'message' => 'Document has been successfully submitted.',
                'data' => new DocumentResource($document),
            ]);
        }
    }

    // Fetch other documents for the user
    function fetchOtherDocuments() {
        $documents = auth()->user()->userDocuments()->where('documentable_type', 'App\Models\User\User')
        ->where('document_library_id', 5)->get();
        
        return DocumentResource::collection($documents);
    }
}
