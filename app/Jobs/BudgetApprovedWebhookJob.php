<?php

namespace App\Jobs;

use App\Models\Accounting\Budget;
use App\Models\Accounting\Budgetitem;
use App\Models\Building\Building;
use App\Models\Master\Service;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BudgetApprovedWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $propertyGroupId,protected $periodCode)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $propertyGroupId =$this->propertyGroupId;
        $periodCode = $this->periodCode;
        $results = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get("https://qagate.dubailand.gov.ae/mollak/external/sync/budgets/".$propertyGroupId."/all/".$periodCode);
        $responce = $results->json()['response'];
        Log::info($responce);
        
            $building = Building::where('property_group_id',$responce['propertyGroupId'])->first();
            foreach($responce['serviceChargeGroups'] as $group){
                $budget = Budget::firstOrCreate([
                    'building_id' => $building?->id,
                    'budget_period' => $group['budgetPeriodCode'],
                ],[
                    'budget_from' => Carbon::parse($group['budgetPeriodFrom'])->toDateString() ,
                    'budget_to' => Carbon::parse($group['budgetPeriodTo'])->toDateString(),
                ]);
                foreach($group['budgetItems'] as $budgetItem){
                    $service =Service::where('code',$budgetItem['serviceCode'])->first();
                    $item = Budgetitem::updateOrCreate([
                        'service_id'=>$service?->id,
                        'budget_id'=>$budget->id,
                    ],[
                        'total' => intval($budgetItem['totalCost']),
                        'vat_rate' => 0.05,
                        'budget_excl_vat' => intval($budgetItem['totalCost']) - intval($budgetItem['vatAmount']),
                        'vat_amount' => intval($budgetItem['vatAmount']),
                    ]);
                }
            }
    }
}
