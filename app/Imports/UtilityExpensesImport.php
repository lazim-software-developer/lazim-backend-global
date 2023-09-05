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
            // Initialize the utility if it's not yet in our data array
            $this->data[$reference] = [
                'utility_reference' => $row['utility_reference'],
                'amount'            => $row['amount'],
                'utility_name'      => $row['utility_name'],
                'provider_name'     => $row['provider_name'],
                'trend'             => [],
            ];
        }

        // Append to the trend for the respective utility
        $this->data[$reference]['trend'][] = [
            'duration'      => $row['duration'],
            'duration_str'  => $row['duration_str'],
            'amount'        => $row['trend_amount'],
        ];
    }

    public function getResults(): array
    {
        return array_values($this->data);
    }
}
