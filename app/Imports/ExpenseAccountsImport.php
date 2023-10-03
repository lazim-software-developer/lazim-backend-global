<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExpenseAccountsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return [

            'actual'  => $row['actual'],
            'budget' => $row['budget'],
            'variance'   => $row['variance'],
            'Service_Code'   => $row['Service_Code'],

        ];
    }
}
