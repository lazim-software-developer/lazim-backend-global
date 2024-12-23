<?php

namespace App\Filament\Resources\ComplaintResource\Pages;

use App\Filament\Resources\ComplaintResource;
use App\Models\Master\Service;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateComplaint extends CreateRecord
{
    protected static string $resource = ComplaintResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $serviceName = Service::where('id', $data['service_id'])->value('name');

        $data['priority']             = 3;
        $data['status']               = 'open';
        $data['complaintable_type']   = 'App\Models\Vendor\Vendor';
        $data['complaintable_id']     = auth()->user()->id;
        $data['user_id']              = auth()->user()->id;
        $data['owner_association_id'] = auth()->user()->owner_association_id;
        $data['category']             = $serviceName;
        $data['ticket_number']        = generate_ticket_number("CP");
        $data['complaint_type']       = 'preventive_maintenance';
        $data['open_time']            = Carbon::now();
        return $data;
    }

    protected function afterCreate(): void
    {
        $complaint = $this->record;

        // Save media files
        if($this->data['media'] ?? null) {
            foreach($this->data['media'] as $file) {
                $complaint->media()->create([
                    'name' => 'before',
                    'url' => $file,
                ]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
