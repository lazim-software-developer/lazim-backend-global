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
        ])->get("https://qagate.dubailand.gov.ae/mollak/external/sync/managementcompany/" . 54713 . "/propertygroups");

        // Decode the API response
        $data = $propertyResults->json();
        
        $chargePeriodResults = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get("https://qagate.dubailand.gov.ae/mollak/external/sync/invoices/" . 235553 . "/servicechargeperiods");

        // Decode the API response
        $data1 = $chargePeriodResults->json();
        // Return the transformed data using the API resource
        // return PropertyGroupResource::collection($data['response']['propertyGroups']);

        // $hardcodedPropertyGroups = [
        //     ['id' => 235553, 'name' => 'Sunshine Residence'],
        //     ['id' => 236899, 'name' => 'Sunbeam'],
        //     ['id' => 1089946, 'name' => 'APEX ATRIUM BUILDING'],
        //     ['id' => 1106135, 'name' => 'SPANISH TWIN VILLA'],
        //     ['id' => 111782142, 'name' => 'Suntech Tower'],
        //     ['id' => 146995554, 'name' => 'Empire Heights'],
        //     ['id' => 182636537, 'name' => 'Suncity Homes'],
        //     ['id' => 208393821, 'name' => 'Continental Tower'],
        //     ['id' => 306418560, 'name' => 'Samana Greens'],
        //     ['id' => 311139905, 'name' => '2020 Marquis'],
        //     ['id' => 353741613, 'name' => 'Azure'],
        //     ['id' => 443715721, 'name' => 'East40'],
        //     ['id' => 456632490, 'name' => 'Royal Residence'],
        //     ['id' => 494741520, 'name' => 'SAFEER TOWER 2'],
        //     ['id' => 571737916, 'name' => 'WAVES TOWER'],
        //     ['id' => 591473609, 'name' => 'Myka Residence'],
        //     ['id' => 600776909, 'name' => 'Rukan'],
        //     ['id' => 687874294, 'name' => 'The Court']
        // ];

        // $hardcodedData = [
        //     ['id' => 704518, 'name' => '1-Jan-2018 To 31-Dec-2018', 'from' => '2018-01-01T20:00:00', 'to' => '2018-12-31T20:00:00'],
        //     ['id' => 3510558, 'name' => '1-Jan-2019 To 31-Dec-2019', 'from' => '2019-01-01T20:00:00', 'to' => '2019-12-31T20:00:00'],
        //     ['id' => 105961883, 'name' => 'Provisional-1-Jan-2020 To 31-Mar-2020', 'from' => '2020-01-01T00:00:00', 'to' => '2020-03-31T00:00:00'],
        //     ['id' => 113504684, 'name' => '1-Jan-2020 To 31-Dec-2020', 'from' => '2020-01-01T20:00:00', 'to' => '2020-12-31T20:00:00'],
        //     ['id' => 186582794, 'name' => '1-Jan-2021 To 31-Dec-2021', 'from' => '2021-01-01T20:00:00', 'to' => '2021-12-31T20:00:00'],
        //     ['id' => 453522143, 'name' => '1-Jan-2022 To 31-Dec-2022', 'from' => '2022-01-01T20:00:00', 'to' => '2022-12-31T20:00:00'],
        //     ['id' => 604334600, 'name' => '1-Jan-2023 To 31-Dec-2023', 'from' => '2023-01-01T20:00:00', 'to' => '2023-12-31T20:00:00']
        // ];
            $datas = PropertyGroupResource::collection($data['response']['propertyGroups']);
            // Log::info(json_decode($datas));
            dd($datas);
        return [
            'services' => ServiceParameterResource::collection(ServiceParameter::all()),
            'propertyGroups' => PropertyGroupResource::collection($data['response']['propertyGroups']),
            'propertyPeriods' => ServicePeriodResource::collection($data1['response']['serviceChargePeriod']),
            // 'propertyGroups' => $hardcodedPropertyGroups,
            // 'propertyPeriods' => $hardcodedData
            
        ];
    }

    public function table(Table $table): Table
    {   
        return $table
            ->query(OaServiceRequest::query()->where('status','Success'))
            ->columns([
                TextColumn::make('property_name'),
                TextColumn::make('service_period'),
                TextColumn::make('status'),
            ]);
    }
}
