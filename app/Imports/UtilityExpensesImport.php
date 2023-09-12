<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UtilityExpensesImport implements ToModel, WithHeadingRow
{
    public $data = [];

    public function model(array $row)
    {
        $reference = $row['utility_reference'];

        if (!isset($this->data[$reference])) {
            if(isset($row['amount']) && isset($row['utility_name']) && isset($row['provider_name'])) {
                // Initialize the utility if it's not yet in our data array
                $this->data[$reference] = [
                    'utility_reference' => (string)$row['utility_reference'],
                    'amount'            => (float)$row['amount'],
                    'utility_name'      => (string)$row['utility_name'],
                    'provider_name'     => (string)$row['provider_name'],
                    'trend'             => [],
                ];
            }
        }

        // Append to the trend for the respective utility
        if(isset($row['duration']) && isset($row['duration_str']) && isset($row['trend_amount'])) {
            $this->data[$reference]['trend'][] = [
                'duration'      => (string)$row['duration'],
                'duration_str'  => (string)$row['duration_str'],
                'amount'        => (float)$row['trend_amount'],
            ];
        }
    }

    public function getResults(): array
    {
        return array_values($this->data);
    }
}
