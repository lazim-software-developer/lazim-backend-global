<?php

namespace App\Filament\Pages;

use App\Http\Resources\Master\PropertyGroupResource;
use App\Http\Resources\Master\ServicePeriodResource;
use App\Http\Resources\ServiceParameterResource;
use App\Models\OaServiceRequest;
use App\Models\ServiceParameter;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ZipArchive;

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

        // $chargePeriodResults = Http::withOptions(['verify' => false])->withHeaders([
        //     'content-type' => 'application/json',
        //     'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        // ])->get("https://qagate.dubailand.gov.ae/mollak/external/sync/invoices/" . 235553 . "/servicechargeperiods");

        // // Decode the API response
        // $data1 = $chargePeriodResults->json();
        // Return the transformed data using the API resource
        // return PropertyGroupResource::collection($data['response']['propertyGroups']);

        // $hardcodedPropertyGroups = [
        //     ['propertyGroupId' => 235553, 'propertyGroupName' => ['englishName' => 'Sunshine Residence']],
        //     ['propertyGroupId' => 236899, 'propertyGroupName' => ['englishName' => 'Sunbeam']],
        //     ['propertyGroupId' => 1089946, 'propertyGroupName' => ['englishName' => 'APEX ATRIUM BUILDING']],
        //     ['propertyGroupId' => 1106135, 'propertyGroupName' => ['englishName' => 'SPANISH TWIN VILLA']],
        //     ['propertyGroupId' => 111782142, 'propertyGroupName' => ['englishName' => 'Suntech Tower']],
        //     ['propertyGroupId' => 146995554, 'propertyGroupName' => ['englishName' => 'Empire Heights']],
        //     ['propertyGroupId' => 182636537, 'propertyGroupName' => ['englishName' => 'Suncity Homes']],
        //     ['propertyGroupId' => 208393821, 'propertyGroupName' => ['englishName' => 'Continental Tower']],
        //     ['propertyGroupId' => 306418560, 'propertyGroupName' => ['englishName' => 'Samana Greens']],
        //     ['propertyGroupId' => 311139905, 'propertyGroupName' => ['englishName' => '2020 Marquis']],
        //     ['propertyGroupId' => 353741613, 'propertyGroupName' => ['englishName' => 'Azure']],
        //     ['propertyGroupId' => 443715721, 'propertyGroupName' => ['englishName' => 'East40']],
        //     ['propertyGroupId' => 456632490, 'propertyGroupName' => ['englishName' => 'Royal Residence']],
        //     ['propertyGroupId' => 494741520, 'propertyGroupName' => ['englishName' => 'SAFEER TOWER 2']],
        //     ['propertyGroupId' => 571737916, 'propertyGroupName' => ['englishName' => 'WAVES TOWER']],
        //     ['propertyGroupId' => 591473609, 'propertyGroupName' => ['englishName' => 'Myka Residence']],
        //     ['propertyGroupId' => 600776909, 'propertyGroupName' => ['englishName' => 'Rukan']],
        //     ['propertyGroupId' => 687874294, 'propertyGroupName' => ['englishName' => 'The Court']]
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
                TextColumn::make('created_at')->label('Date of Submission'),
                TextColumn::make('status'),
            ])
            ->actions([
                Action::make('Download')->button()
                    ->action(function ($record) {
                        $templateMap = [
                            'e_services' => $record->oa_service_file . '/e_services.xlsx',
                            'happiness_center' => $record->oa_service_file . '/happiness_center.xlsx',
                            'balance_sheet' => $record->oa_service_file . '/balance_sheet.xlsx',
                            'general_fund_statement' => $record->oa_service_file . '/general_fund_statement.xlsx',
                            'reserve_fund' => $record->oa_service_file . '/reserve_fund.xlsx',
                            'budget_vs_actual' => $record->oa_service_file . '/budget_vs_actual.xlsx',
                            'accounts_payables' => $record->oa_service_file . '/accounts_payables.xlsx',
                            'delinquents' => $record->oa_service_file . '/delinquents.xlsx',
                            'collections' => $record->oa_service_file . '/collections.xlsx',
                            'bank_balance' => $record->oa_service_file . '/bank_balance.xlsx',
                            'utility_expenses' => $record->oa_service_file . '/utility_expenses.xlsx',
                            'work_orders' => $record->oa_service_file . '/work_orders.xlsx',
                            'asset_list_and_expenses' => $record->oa_service_file . '/asset_list_and_expenses.xlsx',
                        ];
                        $s3Client = new S3Client([
                            'version'     => 'latest',
                            'region'      => 'ap-south-1',
                            'credentials' => [
                                'key'    => env('AWS_ACCESS_KEY_ID'),
                                'secret' => env('AWS_SECRET_ACCESS_KEY'),
                            ],
                            'http'        => [
                                'verify' => false
                            ]
                        ]);
                        $bucket = env('AWS_BUCKET');
                        $zip = new ZipArchive();
                        $zipFileName = 'documents.zip';
                        if ($zip->open($zipFileName, ZipArchive::CREATE) === TRUE) {
                            foreach ($templateMap as $name => $key) {
                                try {
                                    $result = $s3Client->getObject([
                                        'Bucket' => $bucket,
                                        'Key'    => $key
                                    ]);
                                    $fileContent = $result['Body']->getContents();
                                    $zip->addFromString(basename($key), $fileContent);
                                } catch (AwsException $e) {
                                    return response()->json(['error' => $e->getMessage()], 500);
                                }
                            }
                            $zip->close();
                
                            return response()->download($zipFileName)->deleteFileAfterSend(true);
                        }
                    })
            ]);
    }
}
