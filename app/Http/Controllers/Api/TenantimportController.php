<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Building\FlatTenant;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;

class TenantimportController extends Controller
{
    public function import(Request $request)
    {
        $uploadedFile = $request->file('file');
        $filetype = strpos($uploadedFile->getClientOriginalName(),'.xlsx');
        dd($filetype);
        if($filetype)
        {
            $collection = (new FastExcel)->import($uploadedFile,function ($line) {
                return FlatTenant::create([
                    'flat_id' => $line['Flat'],
                    'tenant_id' => $line['User'],
                    'building_id' => $line['Building'],
                    'start_date' => $line['Start date'],
                    'end_date' => $line['End date'],
                ]);
            });
        }
        return [
            'Status' => 'Done'
        ];
    }
}
