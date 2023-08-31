<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BlanceSheetImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => new IncomeImport(),
            1 => new ExpenseImport(),
            2 => new AssetImport(),
            3 => new LiabilityImport(),
            4 => new EquityImport(),
        ];
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        //
    }
}
