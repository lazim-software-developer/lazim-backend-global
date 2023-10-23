<?php

namespace App\Filament\Resources\SnaggingResource\Pages;

use App\Filament\Resources\SnaggingResource;
use App\Models\Building\Complaint;
use App\Models\User\User;
use Doctrine\DBAL\Types\JsonType;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateSnagging extends CreateRecord
{
    protected static string $resource = SnaggingResource::class;
    protected ?string $heading        = 'Snagging';
    protected function afterCreate()
    {

        $user   = Filament::auth()->id();

        // $jsonValue = json_encode(['comment' => $this->record->remarks, 'date' => now(),
        //     'user'                              => User::where('id', $this->record->user_id)->first()->first_name,
        // ]);

        // Complaint::where('id', $this->record->id)
        //     ->update([

        //         'remarks' => $jsonValue,
        //     ]);
        $type = $this->data['complaintable_type'];
        $id   = $this->data['complaintable_id'];

        Complaint::where('id', $this->record->id)
            ->update([
                'complaintable_type' => $type,
                'complaintable_id'   => $id,
                'user_id'            => 1,
                'open_time'          => now()->timezone('Asia/Kolkata'),
            ]);
        $status = $this->record->status;
        if ($status == 'completed') {
            Complaint::where('id', $this->record->id)
                ->update([
                    'close_time' => now()->timezone('Asia/Kolkata'),
                ]);
        }

    }
}
