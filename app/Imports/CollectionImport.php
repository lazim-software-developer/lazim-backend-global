<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class CollectionImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            if ($row['section'] === 'by_method') {
                $this->data['by_method'][] = [
                    'payment_method_id' => $row['payment_method_id'],
                    'amount'            => $row['amount'],
                ];
            } elseif ($row['section'] === 'recovery') {
                $this->data['recovery'] = [
                    'opening_balanace'  => $row['opening_balanace'],
                    'charge'            => $row['charge'],
                    'payment'           => $row['payment'],
                    'closing_balance'   => $row['closing_balance'],
                    'rate'              => $row['rate']
                ];
            }
        }
    }
}
