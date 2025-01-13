<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use Filament\Resources\Pages\EditRecord;

class EditBill extends EditRecord
{
    protected static string $resource = BillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $bill       = $this->record;
        $dewaNumber = $bill->bill_number;
        $flatId     = $bill->flat['property_number'];
        $buildingId = Flat::where('property_number', $flatId)->pluck('building_id')[0];
        $building   = Building::where('id', $buildingId)->pluck('name')[0];

        return [
            'building_id'       => $building,
            'type'              => $bill->type,
            'amount'            => $bill->amount,
            'month'             => $bill->month,
            'due_date'          => $bill->due_date,
            'uploaded_on'       => $bill->uploaded_on,
            'status'            => $bill->status,
            'uploaded_by'       => $bill->uploaded_by,
            'status_updated_by' => $bill->status_updated_by,
            'bill_number'       => $dewaNumber ?? null,

        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record['type'] != 'DEWA') {
            if ($data['status'] != $this->record['status']) {
                $data['status_updated_by'] = auth()->id();
            }

        }

        return $data;
    }

    protected function getRedirectUrl(): string | null
    {
        return $this->getResource()::getUrl('index');
    }
}
