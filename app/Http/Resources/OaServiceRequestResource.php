<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OaServiceRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'service_parameter' => $this->serviceParameter->name,
            'property_group'    => $this->property_group,
            'from_date'         => $this->from_date,
            'to_date'           => $this->to_date,
            'status'            => $this->status,
            'property_name'     => $this->property_name,
            'service_period'    => $this->service_period,
            'created_date'      => $this->created_at,
            'oa_service_file'   => isset($this->oa_service_file) ?
            [
                'e_services'              => env('AWS_URL') . '/' . $this->oa_service_file . '/e_services.xlsx',
                'happiness_center'        => env('AWS_URL') . '/' . $this->oa_service_file . '/happiness_center.xlsx',
                'balance_sheet'           => env('AWS_URL') . '/' . $this->oa_service_file . '/balance_sheet.xlsx',
                'reserve_fund'            => env('AWS_URL') . '/' . $this->oa_service_file . '/reserve_fund.xlsx',
                'budget_vs_actual'        => env('AWS_URL') . '/' . $this->oa_service_file . '/budget_vs_actual.xlsx',
                'accounts_payables'       => env('AWS_URL') . '/' . $this->oa_service_file . '/accounts_payables.xlsx',
                'delinquents'             => env('AWS_URL') . '/' . $this->oa_service_file . '/delinquents.xlsx',
                'collections'             => env('AWS_URL') . '/' . $this->oa_service_file . '/collections.xlsx',
                'work_orders'             => env('AWS_URL') . '/' . $this->oa_service_file . '/work_orders.xlsx',
                'general_fund_statement'  => env('AWS_URL') . '/' . $this->oa_service_file .
                '/general_fund_statement.xlsx',
                'bank_balance'            => env('AWS_URL') . '/' . $this->oa_service_file . '/bank_balance.xlsx',
                'asset_list_and_expenses' => env('AWS_URL') . '/' . $this->oa_service_file .
                '/asset_list_and_expenses.xlsx',
                'utility_expenses'        => env('AWS_URL') . '/' . $this->oa_service_file . '/utility_expenses.xlsx',
            ] : null,
        ];
    }
}
