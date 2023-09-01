<?php

namespace Database\Seeders;

use App\Models\ServiceParameter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceParameterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceparameters = [
            ['id' => 1, 'name' => 'Services Requests', 'active' => 1],
            ['id' => 2, 'name' => 'Happiness Center', 'active' => 1],
            ['id' => 3, 'name' => 'Balance Sheet', 'active' => 1],
            ['id' => 4, 'name' => 'Central Fund  Statement', 'active' => 1],
            ['id' => 5, 'name' => 'Reserve Fund Statement', 'active' => 1],
            ['id' => 6, 'name' => 'Budget Vs Actual', 'active' => 1],
            ['id' => 7, 'name' => 'Accounts Payables', 'active' => 1],
            ['id' => 8, 'name' => 'Delinquent Owners', 'active' => 1],
            ['id' => 9, 'name' => 'Collection Report', 'active' => 1],
            ['id' => 10, 'name' => 'Bank Balance', 'active' => 1],
            ['id' => 11, 'name' => 'Utility Expenses', 'active' => 1],
            ['id' => 12, 'name' => 'Work Orders', 'active' => 1],
            ['id' => 13, 'name' => 'Asset List and Expenses', 'active' => 1]
        ];

        ServiceParameter::insert($serviceparameters);
    }
}
