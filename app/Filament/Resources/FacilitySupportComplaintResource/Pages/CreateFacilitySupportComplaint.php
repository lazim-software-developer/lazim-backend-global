<?php

namespace App\Filament\Resources\FacilitySupportComplaintResource\Pages;

use App\Filament\Resources\FacilitySupportComplaintResource;
use App\Models\Building\FlatTenant;
use App\Models\Master\Service;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;

class CreateFacilitySupportComplaint extends CreateRecord
{
    protected static string $resource = FacilitySupportComplaintResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $serviceName = Service::where('id', $data['service_id'])->value('name');
        $userId = FlatTenant::where('flat_id', $data['flat_id'])->pluck('tenant_id')->first();

        $data['priority']             = 3;
        $data['status']               = 'open';
        $data['complaintable_type']   = FlatTenant::class;
        $data['complaintable_id']     = auth()->user()->id;
        $data['user_id']              = $userId ?? auth()->user()->id;
        $data['owner_association_id'] = auth()->user()->owner_association_id;
        $data['category']             = $serviceName;
        $data['ticket_number']        = generate_ticket_number("CP");
        $data['complaint_type']       = 'help_desk';
        $data['open_time']            = Carbon::now();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
