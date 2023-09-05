<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AssetsImport implements ToModel, WithHeadingRow
{
    public $data = [];

    public function model(array $row)
    {
        // Check if asset already exists
        if (!isset($this->data[$row['asset_name']])) {
            $this->data[$row['asset_name']] = [
                'name'  => $row['asset_name'],
                'items' => []
            ];
        }

        // Append item to asset
        $this->data[$row['asset_name']]['items'][] = [
            'name'                   => $row['item_name'],
            'asset_code'             => $row['asset_code'],
            'location'               => $row['location'] ?? null, // Assuming there might be empty locations
            'warranties_count'       => $row['warranties_count'],
            'active_warranties_count'=> $row['active_warranties_count'],
            'jobs_count'             => $row['jobs_count'],
            'expenses'               => $row['expenses']
        ];
    }

    public function getResults(): array
    {
        // Convert the associative array into indexed array
        return array_values($this->data);
    }
}


