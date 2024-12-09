<?php

namespace App\Http\Controllers\Documents;

use App\Models\Media;
use App\Models\User\User;
use Illuminate\Http\Request;
use App\Models\Building\Building;
use App\Models\Building\Document;
use Illuminate\Support\Facades\DB;
use App\Models\Building\FlatTenant;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Master\DocumentLibrary;
use App\Http\Requests\MakaniNumberRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Requests\Document\DocumentRequest;
use App\Http\Resources\Documents\DocumentResource;
use App\Http\Resources\Documents\DocumentLibraryResource;

class DocumentsController extends Controller
{
    public function index()
    {
        $documents = DocumentLibrary::where('label', 'master')->get();
        return DocumentLibraryResource::collection($documents);
    }
    public function tenantDocuments(Request $request)
    {
        $request->validate([
            'flat_id'     => 'required|exists:flats,id',
            'building_id' => 'required|exists:buildings,id',
        ]);
        $user       = auth()->user();
        $flatTenant = FlatTenant::where([
            'tenant_id'   => $user->id,
            'building_id' => $request->building_id,
            'flat_id'     => $request->flat_id,
            'active'      => true,
        ])->first();
        abort_if($flatTenant->role !== 'Owner', 403, 'You are not Owner');

        // Get users with their names
        $users = User::whereIn('id', FlatTenant::where([
            'building_id' => $request->building_id,
            'flat_id'     => $request->flat_id,
            'active'      => true,
            'role'        => 'Tenant',
        ])->pluck('tenant_id'))->select('id', 'first_name')->get();

        Log::info('Users: ' . $users);

        $documentLibraries = DocumentLibrary::where('label', 'master')->get();

        // Get the latest documents for each user and document type
        $documents = Document::whereIn('documentable_id', $users->pluck('id'))
            ->where(['documentable_type' => User::class])
            ->whereIn('document_library_id', $documentLibraries->pluck('id'))
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy(['documentable_id', 'document_library_id'])
            ->map(function ($userDocs) {
                return $userDocs->map(function ($docs) {
                    return $docs->first();
                });
            });
        Log::info('Documents: ' . $documents);

        return response()->json([
            'users' => $users->map(function ($user) use ($documents, $documentLibraries) {
                $userDocs = $documents[$user->id] ?? collect();
                return [
                    'user_id' => $user?->id,
                    'user_name' => $user?->first_name,
                    'documents' => DocumentLibraryResource::collection($documentLibraries)->map(function ($resource) use ($user) {
                        return $resource->additional(['tenant_id' => $user?->id]);
                    })
                ];
            })
        ]);
    }
    public function create(DocumentRequest $request)
    {
        $currentDate = date('Y-m-d');

        $building = DB::table('building_owner_association')->where(['building_id' => $request->building_id,'active'=> true])->first();

        $document = Document::create([
            'document_library_id' => $request->document_library_id,
            'building_id' => $request->building_id,
            'documentable_id' => auth()->user()->id,
            'status' => 'submitted',
            'expiry_date' => date('Y-m-d', strtotime('+1 year', strtotime($currentDate))), //to do need to make changes for expiry date
            'documentable_type' => User::class,
            'name' => $request->name,
            'flat_id' => $request->flat_id ?? null,
            'owner_association_id' => $building?->owner_association_id
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

    public function makaniNumber(MakaniNumberRequest $request)
    {
        $currentDate = date('Y-m-d');
        $building = DB::table('building_owner_association')->where(['building_id' => $request->building_id, 'active' => true])->first();

        // Check if document already exists
        $existingDocument = Document::where([
            'document_library_id' => $request->document_library_id,
            'building_id' => $request->building_id,
            'flat_id' => $request->flat_id,
            'documentable_id' => auth()->user()->id,
            'documentable_type' => User::class,
        ])->first();

        $documentData = [
            'document_library_id'  => $request->document_library_id,
            'building_id'          => $request->building_id,
            'documentable_id'      => auth()->user()->id,
            'status'               => 'submitted',
            'expiry_date'          => date('Y-m-d', strtotime('+1 year', strtotime($currentDate))),
            'documentable_type'    => User::class,
            'name'                 => $request->name,
            'flat_id'              => $request->flat_id ?? null,
            'owner_association_id' => $building?->owner_association_id,
            'url'                  => $request->number ?? null,
        ];

        if ($existingDocument) {
            $existingDocument->update($documentData);
            $message = 'Makani number updated successfully';
        } else {
            Document::create($documentData);
            $message = 'Makani number added successfully';
        }

        return new CustomResponseResource([
            'title'   => $message,
            'message' => $message,
            'code'    => 200,
        ]);
    }

    // Fetch other documents for the user
    function fetchOtherDocuments()
    {
        $documents = auth()->user()->userDocuments()->where('documentable_type', 'App\Models\User\User')
            ->where('document_library_id', 5)->get();

        return DocumentResource::collection($documents);
    }
}
