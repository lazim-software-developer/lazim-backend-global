<?php

namespace App\Http\Controllers;

use App\Models\ApartmentOwner;
use Illuminate\Support\Facades\Storage;

class OwnerImportController extends Controller
{
    // public function downloadSampleFile()
    // {
    //     $filePath = 'sample-files/sample-import-owners-records.csv';
    //     if (!Storage::exists($filePath)) {
    //         abort(404, 'Sample file not found.');
    //     }

    //     return response()->streamDownload(
    //         function () use ($filePath) {
    //             echo Storage::get($filePath);
    //         },
    //         'sample-import-owners-records.csv',
    //         [
    //             'Content-Type' => 'text/csv',
    //             'Content-Disposition' => 'attachment; filename="sample-import-owners-records.csv"'
    //         ]
    //     );
    // }

    public function downloadSampleFile()
    {
        $owners = ApartmentOwner::select('id', 'name', 'email', 'mobile')->get();

        $csv = implode(',', ['Owner Number', 'Name', 'Mobile', 'Email', 'Passport', 'National ID', 'Trade License Number', 'Building', 'Unit Number']) . "\n";

        foreach ($owners as $owner) {
            $unitNumbers = $owner->flatOwners
                ->pluck('flat.property_number')
                ->filter()
                ->implode(' | ');
            $csv .= implode(',', [
                ' ',
                ' ',
                ' ',
                ' ',
                ' ',
                ' ',
                ' ',
                ' ',
                $unitNumbers ?: ' ',
            ]) . "\n";
        }

        $filename = 'sample-import-owners-records.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
