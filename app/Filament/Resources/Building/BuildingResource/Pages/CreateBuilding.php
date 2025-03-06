<?php

namespace App\Filament\Resources\Building\BuildingResource\Pages;

use App\Models\Floor;
use Filament\Actions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Building\BuildingResource;

class CreateBuilding extends CreateRecord
{
    protected static string $resource = BuildingResource::class;
  
  protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (array_key_exists('search', $data)) {
            $data['address'] = $data['search'];
        }

        $data['show_inhouse_services'] = 0;
        $data['managed_by']        = 'Property Manager';
        return $data;
    }
    public function afterCreate()
    {
        $this->CreateFloor($this->record);
        $this->building_owner_association($this->record);
        $this->LazimAccountDatabase($this->record);
    }

    public function CreateFloor($data)
    {
        if ($this->record->floors != null && $this->record->floors> 0) {
            $countfloor = $this->record->floors;
            while ($countfloor > 0) {
                // Build an object with the required properties
                $qrCodeContent = [
                    'floors' => $countfloor,
                    'building_id' => $this->record->id,
                ];
                // Generate a QR code using the QrCode library
                $qrCode = QrCode::size(200)->generate(json_encode($qrCodeContent));
                Floor::create([
                    'floors' => $countfloor,
                    'building_id' => $this->record->id,
                    'qr_code' => $qrCode,
                ]);
                $countfloor = $countfloor - 1;
            }
        }
    }
    public function building_owner_association($data)
    {
        DB::table('building_owner_association')->updateOrInsert([
            'owner_association_id' => $data->owner_association_id,
            'building_id' => $data->id,
            'from'                 => $this->data['from'],
            'to'                   => $this->data['to'],
            'active'               => true,
        ]);
    }

    public function LazimAccountDatabase($data)
    {
        $connection = DB::connection('lazim_accounts');
        $created_by = $connection->table('users')->where('owner_association_id', $data->owner_association_id)->where('type', 'company')->first()?->id;
        $connection->table('users')->updateOrInsert([
            'building_id' => $data->id,
            'owner_association_id' => $data->owner_association_id,
        ],[
            'name' => $data->name,
            'email' => 'user' . Str::random(8) . '@lazim.ae',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'type' => 'building',
            'lang' => 'en',
            'created_by' => $created_by,
            'is_disable' => 0,
            'plan' => 1,
            'is_enable_login' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
