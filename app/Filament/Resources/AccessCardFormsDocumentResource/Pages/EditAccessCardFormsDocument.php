<?php

namespace App\Filament\Resources\AccessCardFormsDocumentResource\Pages;

use App\Filament\Resources\AccessCardFormsDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccessCardFormsDocument extends EditRecord {
    protected static string $resource = AccessCardFormsDocumentResource::class;
    protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index');
    }
    protected function mutateFormDataBeforeFill(array $data): array {
        $parkingDetails = json_decode($data['parking_details'], true);

        $formattedDetails = '';

        if(is_array($parkingDetails)) {
            foreach($parkingDetails as $key => $val) {
                // Accumulate the formatted details with line breaks
                $formattedDetails .= ucfirst(str_replace('_', ' ', $key)).": $val\n";
            }
        } else {
            // Handle the case where parking_details is not an array
            $formattedDetails = "Invalid parking details format";
        }

        // Assign the accumulated content to $data['parking_details']
        $data['parking_details'] = $formattedDetails;

        // Your other logic for data manipulation...

        return $data;
    }

    protected function getHeaderActions(): array {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
}
