<?php

namespace App\Filament\Imports;

use App\Models\ApartmentOwner;
use App\Models\Building\Flat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OwnerImport implements ToCollection, WithHeadingRow
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
            $processedKeys = []; // Excel file duplicates track

            foreach ($rows as $index => $row) {
                if (count($row) != 9) {
                    $this->invalidFile++;
                }

                $rowNumber = $index + 2;

                // 🔹 Excel File Duplicate Check first
                $excelKey = implode('-', [
                    $row['mobile'],
                    $row['email'],
                    $row['passport'],
                    $row['national_id'],
                    $row['trade_license_number'],
                ]);

                if (isset($processedKeys[$excelKey])) {
                    $firstRow = $processedKeys[$excelKey];
                    throw new \Exception("Row $rowNumber: Duplicate data found in Excel file (matches Row $firstRow)");
                }
                $processedKeys[$excelKey] = $rowNumber;

                // 🔹 DB Duplicate Check
                $duplicate = ApartmentOwner::where('mobile', $row['mobile'])
                    ->orWhere('email', $row['email'])
                    ->orWhere('passport', $row['passport'])
                    ->orWhere('emirates_id', $row['national_id'])
                    ->orWhere('trade_license', $row['trade_license_number'])
                    ->first();

                if ($duplicate) {
                    throw new \Exception("Row $rowNumber: Duplicate data exists in database");
                }

                // 🔹 Building
                $building = DB::table('buildings')
                    ->where('name', trim($row['building']))
                    ->first();

                if (!$building) {
                    throw new \Exception("Row $rowNumber: Building not exists in database");
                }

                // 🔹 Flat
                $flat = DB::table('flats')
                    ->where('property_number', trim($row['unit_number']))
                    ->where('building_id', $building->id)
                    ->first();

                if (!$flat) {
                    throw new \Exception("Row $rowNumber: Flat not found for Unit Number");
                }

                // 🔹 Create Owner
                $owner = ApartmentOwner::create([
                    'owner_number'         => $row['owner_number'],
                    'name'                 => $row['name'],
                    'mobile'               => $row['mobile'],
                    'email'                => $row['email'],
                    'passport'             => $row['passport'],
                    'emirates_id'          => $row['national_id'],
                    'trade_license'        => $row['trade_license_number'],
                    'building_id'          => $building->id,
                    'owner_association_id' => auth()->user()?->owner_association_id,
                    'resource'             => 'Lazim',
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);

                // 🔹 Link flat (Repeater)
                $owner->flatOwners()->create([
                    'flat_id' => $flat->id,
                ]);

                $this->successCount++;
            }

            DB::commit(); // ✅ sab sahi, commit
        } catch (\Exception $e) {
            DB::rollBack(); // ❌ koi bhi duplicate ya error -> rollback
            Log::error('Owner Import Failed: ' . $e->getMessage());
            throw $e; // frontend me error show karne ke liye
        }
    }

    protected function handleSkip($rowNumber, $row, $message)
    {
        $this->errors[] = [
            'row_number' => $rowNumber,
            'data' => $row->toArray(),
            'message' => $message,
            'type' => 'skip',
        ];
    }

    public function getResultSummary()
    {
        if ($this->invalidFile > 0) {
            return [
                'status' => 401,
                'error' => 'Invalid File Format!',
            ];
        }

        return [
            'status' => 200,
            'imported' => $this->successCount,
            'skip' => $this->skipCount,
            'error' => $this->errorCount,
            'details' => $this->errors,
        ];
    }
}
