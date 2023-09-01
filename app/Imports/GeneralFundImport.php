<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GeneralFundImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => new IncomeGeneralImport(),
            1 => new ExpenseGeneralImport(),
        ];
    }

}
