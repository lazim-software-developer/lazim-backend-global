<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class WorkOrdersImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            if(isset($row['title']) && isset($row['status']) && isset($row['type']) && isset($row['total_amount']) && isset($row['vendor_name']) && isset($row['category_name'])) {
                $this->data[] = [
                    'title'  => $row['title'],
                    'status' => $row['status'],
                    'type' => $row['type'],
                    'total_amount' => $row['total_amount'],
                    'vendor_name' => $row['vendor_name'],
                    'category_name' => $row['category_name'],
                ];
            }
        }
    }
}
