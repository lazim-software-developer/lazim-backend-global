<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReservedFundImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => new IncomeReservedImport(),
            1 => new ExpenseReservedImport(),

        ];
    }
}
