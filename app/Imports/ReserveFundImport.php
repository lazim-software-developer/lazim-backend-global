<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class ReserveFundImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if ($row['section'] === 'income') {
                $this->data['income'][] = [
                    'service_code' => $row['service_code'],
                    'balance' => $row['balance'],
                ];
            } elseif ($row['section'] === 'expense') {
                $this->data['expense'][] = [
                    'service_code' => $row['service_code'],
                    'balance' => $row['balance'],
                ];
            }
        }
    }
}
