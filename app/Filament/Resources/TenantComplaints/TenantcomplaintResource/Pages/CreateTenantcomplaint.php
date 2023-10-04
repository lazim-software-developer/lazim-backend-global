<?php

namespace App\Filament\Resources\TenantComplaints\TenantcomplaintResource\Pages;

use App\Filament\Resources\TenantComplaints\TenantcomplaintResource;
use App\Models\Building\Complaint;
use App\Models\User\User;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateTenantcomplaint extends CreateRecord
{
    protected static string $resource = TenantcomplaintResource::class;
    protected function afterCreate()
    {
        $tenant    = Filament::getTenant();
        $jsonValue = json_encode(['comment' => $this->record->remarks, 'date' => now(),
            'user'                              => User::where('id', $this->record->user_id)->first()->first_name,
        ]);

        Complaint::where('id', $this->record->id)
            ->update([

                'remarks' => $jsonValue,
            ]);

    }
}
