<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class AccountsPayablesImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            if($row['service_code']&& $row['account_name'] && $row['bill']  && $row['payment'] && $row['opening_balance'] && $row['closing_balance']) {
                $this->data[] = [
                    'service_code'  => $row['service_code'],
                    'account_name' => $row['account_name'],
                    'bill'  => $row['bill'],
                    'payment' => $row['payment'],
                    'opening_balance' => $row['opening_balance'],
                    'closing_balance' => $row['closing_balance'],
                ];
            }
        }
    }
}
