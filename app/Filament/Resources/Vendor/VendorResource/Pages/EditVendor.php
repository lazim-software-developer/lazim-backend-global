<?php

namespace App\Filament\Resources\Vendor\VendorResource\Pages;

use Filament\Actions;
use App\Models\User\User;
use Illuminate\Support\Str;
use App\Models\Vendor\Vendor;
use App\Jobs\VendorRejectionJob;
use Illuminate\Support\Facades\Hash;
use App\Jobs\VendorAccountCreationJob;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Vendor\VendorResource;
use App\Models\VendorRemarks;

class EditVendor extends EditRecord
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
    public function afterSave()
    {
        $userId = User::find($this->record->owner_id);
        $userId->active = $this->record->active;
        $userId->save();
        if($this->record->active){
            VendorRemarks::firstorcreate([
                'vendor_id'  => $this->record->id,
                'status'     => 'active',
                'remarks'    => $this->record->remarks,
                'user_id'    => auth()->user()->id,
            ]);
        }
        else{
            VendorRemarks::firstorcreate([
                'vendor_id'  => $this->record->id,
                'status'     => 'inactive',
                'remarks'    => $this->record->remarks,
                'user_id'    => auth()->user()->id,
            ]);
        }
        if ($this->record->status !== null) 
        {
            if ($this->record->status == 'rejected') {
                $vendor=Vendor::where('id',$this->record->id)->first();
                $user = User::find($vendor->owner_id);
                $remarks= $this->record->remarks;
                VendorRejectionJob::dispatch($user,$remarks);
            }
            if ($this->record->status == 'approved') 
            {
                $vendor=Vendor::where('id',$this->record->id)->first();
                $user = User::find($vendor->owner_id);
                $password = Str::random(12);
                $user->password = Hash::make($password);
                $user->save();
                VendorAccountCreationJob::dispatch($user, $password);
            }
        }
    }
}
