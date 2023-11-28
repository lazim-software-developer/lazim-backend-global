<?php

namespace App\Imports;

use App\Models\Accounting\SubCategory;
use App\Models\Master\Service;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ServicesImport implements ToCollection, WithHeadingRow
{
    use Importable;

    public $data = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            if ($row['subcategory_name'] && $row['subcategory_name'] !== '' && $row['name'] !== '' && $row['code'] !== '') {
                $subCategory = SubCategory::firstWhere('name', $row['subcategory_name']);
                Log::info('Here', [$row]);
                if ($subCategory) {
                    Service::Create([
                        'subcategory_id' => $subCategory->id,
                        'name' => $row['name'],
                        'type' => 'vendor_service',
                        'code' => $row['code'],
                        'active' => 1
                    ]);
                }
            }
        }
    }
}
