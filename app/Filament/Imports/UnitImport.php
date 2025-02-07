<?php

namespace App\Filament\Imports;

use App\Models\Floor;
use Illuminate\Support\Str;
use App\Models\Building\Flat;
use App\Models\Building\Building;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UnitImport implements ToCollection, WithHeadingRow
{
    protected $successCount = 0;
    protected $skipCount = 0;
    protected $errorCount = 0;
    protected $invalidFile = 0;
    protected $errors = [];

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                if(count($row)!=13){
                    $this->invalidFile++;
                }
                $rowNumber = $index + 2; // Adding 2 because index starts at 0 and we skip header
                // Find building ID by name
                $building = DB::table('buildings')
                ->where('name', trim($row['building']))
                ->first();
                if(empty($building)){
                    $this->skipCount++;
                    $this->handleSkip($rowNumber, $row, 'Building not exists in our database');
                    continue;
                }

                // Find owner association ID by name
                $ownerAssociation = DB::table('owner_associations')
                    ->where('name', trim($row['owner_association']))
                    ->first();
                if(empty($ownerAssociation)){
                    $this->skipCount++;
                    $this->handleSkip($rowNumber, $row, 'Owner Association not exists in our database');
                    continue;
                }

                // Check if building already exists based on multiple criteria
                $existingBuilding = Flat::where([
                    'floor' => $row['floor'],
                    'building_id' => $building->id,
                    'owner_association_id' => $ownerAssociation->id,
                ])->first();

                if ($existingBuilding) {
                    $this->skipCount++;
                    $this->handleSkip($rowNumber, $row, 'Unit already exists');
                    continue;
                }

                
                    Flat::create([
                    'floor'                  => $row['floor'],
                    'building_id'            => $building->id,
                    'owner_association_id'   => $ownerAssociation->id,
                    'description'            => $row['description'],
                    'property_number'        => $row['property_number'],
                    'property_type'          => $row['property_type'],
                    'suit_area'              => $row['suit_area'],
                    'actual_area'            => $row['actual_area'],
                    'balcony_area'           => $row['balcony_area'],
                    'applicable_area'        => $row['applicable_area'],
                    'virtual_account_number' => $row['virtual_account_number'],
                    'parking_count'          => $row['parking_count'],
                    'plot_number'            => $row['plot_number'],
                    'status'                 => 1,
                    'resource'               => 'Default',
                    'created_by'                 => auth()->user()?->id,
                    'updated_by'                 => auth()->user()?->id,
                ]);

                $this->successCount++;
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
        }else{
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