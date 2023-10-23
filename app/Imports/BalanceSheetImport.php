<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class BalanceSheetImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            if ($row['section'] === 'income') {
                $this->data['income'][] = [
                    'name' => $row['name'],
                    'balance' => $row['balance'],
                    'code' => $row['code'],
                ];
            } elseif ($row['section'] === 'expense') {
                $this->data['expense'][] = [
                    'name' => $row['name'],
                    'balance' => $row['balance'],
                ];
            }elseif ($row['section'] === 'asset') {
                $this->data['asset'][] = [
                    'name' => $row['name'],
                    'balance' => $row['balance'],
                ];
            }elseif ($row['section'] === 'liability') {
                $this->data['liability'] []= [
                    'name' => $row['name'],
                    'balance' => $row['balance'],
                ];
            }elseif ($row['section'] === 'equity') {
                $this->data['equity'] []= [
                    'name' => $row['name'],
                    'balance' => $row['balance'],
                ];
            }
        }
    }
}
