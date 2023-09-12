<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class HappinessCenterImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            if($row['happiness_center_id'] && $row['open'] && $row['resolved'] && $row['total']) {
                $this->data[] = [
                    'happiness_center_id'  => $row['happiness_center_id'],
                    'open' => $row['open'],
                    'resolved' => $row['resolved'],
                    'total' => $row['total'],
                ];
            }
        }
    }
}
