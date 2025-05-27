<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BudgetImportController extends Controller
{
    public function downloadSampleBudgetFile()
    {
        $filePath = 'sample-files/service-charge-budget-template.xlsx';

        if (!Storage::exists($filePath)) {
            abort(404, 'Sample file not found.');
        }

        return response()->streamDownload(
            function () use ($filePath) {
                echo Storage::get($filePath);
            },
            'service-charge-budget-template.xlsx',
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="service-charge-budget-template.xlsx"'
            ]
        );
    }
}
