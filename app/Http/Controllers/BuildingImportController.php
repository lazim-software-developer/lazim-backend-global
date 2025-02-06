<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class BuildingImportController extends Controller
{
    public function downloadSampleFile()
    {
        $filePath = 'sample-files/sample-import-buildings-records.csv';

        if (!Storage::exists($filePath)) {
            abort(404, 'Sample file not found.');
        }

        return response()->streamDownload(
            function () use ($filePath) {
                echo Storage::get($filePath);
            },
            'sample-import-buildings-records.csv',
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="sample-import-buildings-records.csv"'
            ]
        );
    }
}