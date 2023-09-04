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
            ['id' => 1, 'name' => 'Services Requests', 'active' => 1,'value' => 'e_services'],
            ['id' => 2, 'name' => 'Happiness Center', 'active' => 1,'value' => 'happiness_center'],
            ['id' => 3, 'name' => 'Balance Sheet', 'active' => 1,'value' => 'balance_sheet'],
            ['id' => 4, 'name' => 'Central Fund  Statement', 'active' => 1,'value' => 'central_fund_statement'],
            ['id' => 5, 'name' => 'Reserve Fund Statement', 'active' => 1,'value' => 'reserve_fund'],
            ['id' => 6, 'name' => 'Budget Vs Actual', 'active' => 1,'value' => 'budget_vs_actual'],
            ['id' => 7, 'name' => 'Accounts Payables', 'active' => 1,'value' => 'accounts_payables'],
            ['id' => 8, 'name' => 'Delinquent Owners', 'active' => 1,'value' => 'delinquents'],
            ['id' => 9, 'name' => 'Collection Report', 'active' => 1,'value' => 'collections'],
            ['id' => 10, 'name' => 'Bank Balance', 'active' => 1,'value' => 'bank_balance'],
            ['id' => 11, 'name' => 'Utility Expenses', 'active' => 1,'value' => 'utility_expenses'],
            ['id' => 12, 'name' => 'Work Orders', 'active' => 1,'value' => 'work_orders'],
            ['id' => 13, 'name' => 'Asset List and Expenses', 'active' => 1,'value' => 'asset_list_and_expenses']
        ];

        ServiceParameter::insert($serviceparameters);
    }
}
