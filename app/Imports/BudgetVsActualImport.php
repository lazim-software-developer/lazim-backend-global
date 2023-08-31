?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BudgetVsActualImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => new IncomeBudgetImport(),
            1 => new ExpenseBudgetImport(),

        ];
    }
}
