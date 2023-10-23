<?php

namespace App\Filament\Resources\Building\ComplaintResource\Pages;

use App\Filament\Resources\Building\ComplaintResource;
use App\Models\Building\Complaint;
use App\Models\User\User;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateComplaint extends CreateRecord
{
    protected static string $resource = ComplaintResource::class;
   protected ?string $heading        = 'Incident Report';
    protected function afterCreate()
    {
        $user = Filament::auth()->id();

        // $jsonValue = json_encode(['comment' => $this->record->remarks, 'date' => now(),
        // 'user' =>User::where('id', $user)->first()->first_name,
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
                'user_id'=>$user,
                'open_time'=>now()->timezone('Asia/Kolkata')
            ]);
        $status=$this->record->status;
        if($status=='completed')
        {
            Complaint::where('id',$this->record->id)
                ->update([
                'close_time'=>now()->timezone('Asia/Kolkata')
            ]);
        }


    }

}
