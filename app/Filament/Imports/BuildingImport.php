<?php

namespace App\Filament\Imports;

use App\Models\Floor;
use Illuminate\Support\Str;
use App\Models\Building\Building;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BuildingImport implements ToCollection, WithHeadingRow
{
    protected $successCount = 0;
    protected $skipCount = 0;
    protected $errorCount = 0;
    protected $invalidFile = 0;
    protected $ownerAssociationError = 0;
    protected $ownerAssociation = '';
    protected $errors = [];

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                if(count($row)!=11){
                    $this->invalidFile++;
                }
                $rowNumber = $index + 2; // Adding 2 because index starts at 0 and we skip header
                // Find owner association ID by name
                $ownerAssociation = DB::table('owner_associations')
                ->where('name', trim($row['owner_association']))
                ->first();
                if(empty($ownerAssociation)){
                    $this->skipCount++;
                    continue;
                }
                // Find City ID by name
                $city = DB::table('cities')
                ->where('name', trim($row['city']))
                ->first();
                if(empty($city)){
                    $this->skipCount++;
                    continue;
                }
                // Check if building already exists based on multiple criteria
                $existingBuilding = Building::where([
                    'name' => $row['name'],
                    'property_group_id' => $row['property_group_id'],
                    'area' => $row['area'],
                    'city_id' => $city->id,
                    'owner_association_id' => $ownerAssociation->id,
                ])->first();

                if ($existingBuilding) {
                    $this->skipCount++;
                    $this->handleSkip($rowNumber, $row, 'Building already exists');
                    continue;
                }

                // Create new building
                $Building=Building::create([
                    'name'                   => $row['name'],
                    'slug'                   => rand(1000,9999).Str::slug($row['name'])??NULL,
                    'property_group_id'      => $row['property_group_id']??NULL,
                    'address_line1'            => $row['address_line_1']??NULL,
                    'address_line2'        => $row['address_line_2']??NULL,
                    'area'          => $row['area'],
                    'city_id'              => $city->id,
                    'description'            => $row['description']??NULL,
                    'floors'           => $row['floors']??NULL,
                    'owner_association_id'        => auth()->user()?->owner_association_id,
                    'allow_postupload' => $row['allow_post_upload']== true ? 1 : 0,
                    'show_inhouse_services'          => $row['show_inhouse_services']== true ? 1 : 0,
                    'resource'            => 'Default',
                    'status'                 => 1,
                    'created_by'                 => auth()->user()?->id,
                    'updated_by'                 => auth()->user()?->id,
                ]);

                $this->successCount++;
                $this->CreateFloor($Building);
                $this->building_owner_association($Building);
                $this->LazimAccountDatabase($Building);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function handleError($rowNumber, $row, $message)
    {
        $this->errorCount++;
        $this->errors[] = [
            'row_number' => $rowNumber,
            'data' => $row->toArray(),
            'message' => $message,
            'type' => 'error'
        ];
    }

    protected function handleSkip($rowNumber, $row, $message)
    {
        $this->errors[] = [
            'row_number' => $rowNumber,
            'data' => $row->toArray(),
            'message' => $message,
            'type' => 'skip'
        ];
    }

    public function getResultSummary()
    {
        if($this->invalidFile>0){
            return [
                'status' => 401,
                'error' => 'Invalid File Format!',
            ];
        }
        else{
        return [
            'status' => 200,
            'imported' => $this->successCount,
            'skip' => $this->skipCount,
            'error' => $this->errorCount,
            'details' => $this->errors
        ];
        }
    }

    public function CreateFloor($data)
    {
        if ($data->floors != null && $data->floors> 0) {
            $countfloor = $data->floors;
            while ($countfloor > 0) {
                // Build an object with the required properties
                $qrCodeContent = [
                    'floors' => $countfloor,
                    'building_id' => $data->id,
                ];
                // Generate a QR code using the QrCode library
                $qrCode = QrCode::size(200)->generate(json_encode($qrCodeContent));
                Floor::firstOrCreate(
                    [
                        'floors' => $countfloor,
                        'building_id' => $data->id,
                    ],
                    [
                        'qr_code' => $qrCode,
                    ]
                );
                $countfloor = $countfloor - 1;
            }
        }
    }
    public function building_owner_association($data)
    {
        DB::table('building_owner_association')->updateOrInsert([
            'owner_association_id' => $data->owner_association_id,
            'building_id' => $data->id,
            'from' => now()->toDateString(),
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
            'is_active' => 1,
            'created_by' => auth()->user()?->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}