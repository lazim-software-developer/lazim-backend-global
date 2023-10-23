<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class IncomeAccountsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return [

            'actual'  => $row['actual'],
            'budget' => $row['budget'],
            'variance'   => $row['variance'],
            'service_Code'   => $row['service_Code'],

        ];
    }
}
