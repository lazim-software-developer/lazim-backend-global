<?php

namespace App\Filament\Resources\LegalNoticeResource\Pages;

use App\Filament\Resources\LegalNoticeResource;
use App\Models\Building\Building;
use App\Models\LegalNotice;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ViewLegalNotice extends ViewRecord
{
    protected static string $resource = LegalNoticeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),

            Action::make('refresh')
                ->label('Refresh Data')
                ->action(function (LegalNotice $legalNotice) {
                    $this->refreshData($legalNotice);
                })
        ];
    }

    protected function refreshData(LegalNotice $legalNotice)
    {
        $building = Building::find($legalNotice->building_id);
        $url = env("MOLLAK_API_URL") . "/sync/legalnotice/" .$building->property_group_id.'/'. $legalNotice->mollakPropertyId.'/'.$legalNotice->registrationNumber.'/rdcdetail';

        try {
            $response = Http::withoutVerifying()->retry(2, 500)->timeout(60)->withHeaders([
                'content-type' => 'application/json',
                'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
            ])->get($url);

            $data = $response->json()['response'];

            if (isset($data['caseStatus']['englishName'])) {
                $legalNotice->case_status = $data['caseStatus']['englishName'];
            }
            if (isset($data['caseNo'])) {
                $legalNotice->case_number = $data['caseNo'];
            }
            if (isset($data['caseType']['englishName'])) {
                $legalNotice->case_type = $data['caseType']['englishName'];
            }

            $legalNotice->save();

            if($response->status() == 200 || $response->status() == 201){

                Notification::make()
                ->title('Details Updated successfully');
            }else{
                Notification::make()
                ->title('Failed to Update Details');
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to Update Details');
        }
    }
}
