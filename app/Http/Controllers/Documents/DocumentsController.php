<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Document\DocumentRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Documents\DocumentLibraryResource;
use App\Http\Resources\Documents\DocumentResource;
use App\Models\Building\Building;
use App\Models\Building\Document;
use App\Models\Master\DocumentLibrary;
use App\Models\Media;
use App\Models\User\User;
use Illuminate\Http\Request;

class DocumentsController extends Controller
{
    public function index()
    {
        $documentLibraries = DocumentLibrary::leftJoin('documents', 'documents.document_library_id', '=', 'document_libraries.id')
            ->select('document_libraries.*', 'documents.name AS document_name', 'documents.status AS document_status') // Select specific columns
            ->get();

    return DocumentLibraryResource::collection($documentLibraries);
    }

    public function create(DocumentRequest $request)
    {

        $currentDate = date('Y-m-d');
        $document = Document::create([
            'document_library_id' => $request->document_library_id,
            'building_id' => $request->building_id,
            // 'documentable_id' => auth()->user()->id,
            'documentable_id' => 2,
            'status' => 'submitted',
            'expiry_date'=>date('Y-m-d', strtotime('+1 year', strtotime($currentDate))),
            'accepted_by'=>1,
            'documentable_type'=>User::class,
            'name' => $request->name,
        ]);

        // Handle multiple images
        if ($request->file('images')) {
               $imagePath = optimizeAndUpload($request->images, 'dev');

                // Create a new media entry for image
                Media::create([
                    'name' => basename($imagePath), // Extracts filename from the full path
                    'url' => $imagePath,
                    'mediaable_id' => $document->id,
                    'mediaable_type'=> 'document'
                ]);

                $document->url = $imagePath;
                $document->save();
        }elseif($request->file('pdf')){
               $pdfPath = optimizeDocumentAndUpload($request->pdf, 'dev');
               // Create a new media entry for pdf
               Media::create([
                'name' => basename($pdfPath), // Extracts filename from the full path
                'url' => $pdfPath,
                'mediaable_id' => $document->id,
                'mediaable_type'=> 'document'
            ]);

            $document->url = $pdfPath;
            $document->save();
        }
            return new CustomResponseResource([
                'title' => 'Document Submitted',
                'message' => 'Document has been successfully submitted.',
                'data' => new DocumentResource($document),
            ]);

    }
}
