<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class ServiceImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            if($row['eservice_id'] && $row['open'] && $row['resolved'] && $row['total']) {
                $this->data[] = [
                    'eservice_id'  => $row['eservice_id'],
                    'open' => $row['open'],
                    'resolved' => $row['resolved'],
                    'total' => $row['total'],
                ];
            }
        }
    }
}
