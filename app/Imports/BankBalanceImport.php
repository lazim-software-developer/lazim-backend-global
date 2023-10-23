<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class BankBalanceImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            if($row['type'] != null) {
                $sectionType = $row['type'];
    
                $this->data[$sectionType] = [
                    'opening_credit'      => $row['opening_credit'],
                    'opening_debit'       => $row['opening_debit'],
                    'opening_balance'     => $row['opening_balance'],
                    'credit'              => $row['credit'],
                    'debit'               => $row['debit'],
                    'balance'             => $row['balance'],
                    'closing_credit'      => $row['closing_credit'],
                    'closing_debit'       => $row['closing_debit'],
                    'closing_balance'     => $row['closing_balance'],
                    'unidentified_credit' => $row['unidentified_credit'],
                    'unidentified_debit'  => $row['unidentified_debit'],
                    'post_dated_credit'   => $row['post_dated_credit'],
                    'post_dated_debit'    => $row['post_dated_debit'],
                ];
            }
        }
    }
}
