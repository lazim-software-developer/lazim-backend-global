<?php

namespace App\Jobs;

use App\Models\Building\Building;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomerCreationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $flat, protected $owner)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $building = Building::find($this->flat->building_id);
            $connection = DB::connection('lazim_accounts');
            // $created_by = $connection->table('users')->where('owner_association_id', $this->flat->owner_association_id)->where('type', 'company')->first()?->id;
            $buildingUser = $connection->table('users')->where(['type' => 'building', 'building_id' => $building->id])->first();
            $customer = $connection->table('customers')->where('created_by', $buildingUser->id)->orderByDesc('customer_id')->first();
            $customerId = $customer ? $customer->customer_id + 1 : 1;
            $name = $this->owner->name . ' - ' . $this->flat->property_number;

            $url = 'api/customer';
            $body = [
                'name' => $name,
                'email' => $this->owner->email,
                'contact' => $this->owner->phone,
                'type' => 'Owner',
                'customer_id' => $customerId,
                'billing_name' => $name,
                'billing_country' => 'UAE',
                'billing_city' => 'Dubai',
                'billing_phone' => $this->owner->phone,
                'billing_address' => $building->address_line1 . ', ' . $building->area,
                'shipping_name' => $name,
                'shipping_country' => 'UAE',
                'shipping_city' => 'Dubai',
                'shipping_phone' => $this->owner->phone,
                'shipping_address' => $building->address_line1 . ', ' . $building->area,
                'created_by_lazim' => true,
                'flat_id' => $this->flat->id,
                'building_id' => $this->flat->building_id,
                'created_by' => $buildingUser?->id,
            ];
            $httpRequest  = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ]);
            Log::info('Job started for flat: ' . $this->flat->id . ' owner: ' . $this->owner->id . 'building' . $this->flat->building_id);
            Log::info('Sending request to API for customer creation', ['request_body' => $body]);
                
            $response = retry(5, function () use ($httpRequest, $url, $body) {
                return $httpRequest->post(env('ACCOUNTING_URL') . $url, $body);
            }, 1000);
            Log::info('Job finished for flat: ' . $this->flat->id . ' owner: ' . $this->owner->id . 'building' . $this->flat->building_id);
        } catch (\Exception $e) {
            Log::error('Error ' . $e->getMessage());
        }
    }
}
