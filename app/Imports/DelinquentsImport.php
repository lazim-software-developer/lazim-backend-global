<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class DelinquentsImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            if(isset($row['unit_number']) && isset($row['recovery_notes_count']) && isset($row['unit_balance']) && isset($row['first_quarter']) && isset($row['second_quarter']) && isset($row['third_quarter'])) {
                $this->data[] = [
                    'unit_number'  => (string)$row['unit_number'],
                    'recovery_notes_count' => $row['recovery_notes_count'],
                    'unit_balance' => $row['unit_balance'],
                    'first_quarter' => $row['first_quarter'],
                    'second_quarter' => $row['second_quarter'],
                    'third_quarter' => $row['third_quarter'],
                ];
            }
        }
    }
}
