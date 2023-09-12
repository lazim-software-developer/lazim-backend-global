<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class BudgetVsActualImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (($row['section']) === 'income_accounts') {
                $this->data['income_accounts'][] = [
                    'actual' => $row['actual'],
                    'budget' => $row['budget'],
                    'variance' => $row['variance'],
                    'service_code' => $row['service_code'],
                ];
            }else if ($row['section'] === 'expense_accounts') {
                $this->data['expense_accounts'][] = [
                    'actual' => $row['actual'],
                    'budget' => $row['budget'],
                    'variance' => $row['variance'],
                    'service_code' => $row['service_code'],
                ];
            }
        }
    
    }
}
