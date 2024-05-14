<?php

namespace App\Filament\Pages;

use App\Http\Resources\Master\PropertyGroupResource;
use App\Http\Resources\Master\ServicePeriodResource;
use App\Http\Resources\ServiceParameterResource;
use App\Models\OaServiceRequest;
use App\Models\ServiceParameter;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Documents extends Page implements HasTable
{
    use InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.documents';

    protected function getViewData(): array
    {
        // $oaId = auth()->user()->ownerAssociation?->mollak_id;

        $propertyResults = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get("/service-charge-period/" . 54713);

        // Decode the API response
        $data = $propertyResults->json();
        Log::info("list----".$data);
        
        // $chargePeriodResults = Http::withOptions(['verify' => false])->withHeaders([
        //     'content-type' => 'application/json',
        //     'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        // ])->get("https://qagate.dubailand.gov.ae/mollak/external/sync/invoices/" . 235553 . "/servicechargeperiods");

        // // Decode the API response
        // $data1 = $chargePeriodResults->json();
        // Return the transformed data using the API resource
        // return PropertyGroupResource::collection($data['response']['propertyGroups']);

            // $datas = PropertyGroupResource::collection($data['response']['propertyGroups']);
            // foreach ($datas as $data){
                
            //     dd($data);
            //     Log::info(json_decode($data));
            // }
            // dd($data['response']['propertyGroups']);
        return [
            'services' => ServiceParameterResource::collection(ServiceParameter::all()),
            'propertyGroups' => $data['response']['propertyGroups'],
            // 'propertyPeriods' => ServicePeriodResource::collection($data1['response']['serviceChargePeriod']),
            // 'propertyGroups' => $hardcodedPropertyGroups,
            // 'propertyPeriods' => $hardcodedData
            
        ];
    }

    public function table(Table $table): Table
    {   
        return $table
            ->query(OaServiceRequest::query()->whereNotNull('created_at'))
            ->columns([
                TextColumn::make('property_name'),
                TextColumn::make('service_period'),
                TextColumn::make('status'),
            ]);
    }
}
