<?php

namespace App\Filament\Resources\ResidentialFormResource\Pages;

use App\Filament\Resources\ResidentialFormResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResidentialForm extends EditRecord
{
    protected static string $resource = ResidentialFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    // protected function mutateFormDataBeforeFill(array $data): array {
    //     $parkingDetails = json_decode($data['emergency_contact'][0], true);

    //     // $formattedDetails = '';

    //     // if(is_array($parkingDetails)) {
    //     //     foreach($parkingDetails as $key => $val) {
    //     //         // Accumulate the formatted details with line breaks
    //     //         $formattedDetails .= ucfirst(str_replace('_', ' ', $key)).": $val\n";
    //     //     }
    //     // } else {
    //     //     // Handle the case where emergency_contact is not an array
    //     //     $formattedDetails = "Invalid parking details format";
    //     // }

    //     // Assign the accumulated content to $data['emergency_contact']
    //     $data['emergency_contact'] = $parkingDetails;

    //     // Your other logic for data manipulation...

    //     return $data;
    // }
}
