<?php

namespace App\Imports;

use App\Models\CoolingAccount;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CoolingAccountImport implements ToCollection, WithHeadingRow
{
    // protected $budgetPeriod;
    protected $buildingId;
    protected $month;

    public function __construct($buildingId,$month)
    {
        
        $this->buildingId = $buildingId;
        $this->month = $month;
    }
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        $date = Carbon::parse($this->month)->format('Y-m-d');
        foreach ($rows as $row) {
            $flatId = $row['unit_no'];
            if(CoolingAccount::where('building_id',$this->buildingId)->where('flat_id',$row['unit_no'])->where('date', $date)->first()->exists()) {
                // dd(CoolingAccount::where('building_id',$this->buildingId)->where('flat_id',$row['unit_no'])->where('date', $date)->first());
                // $message = "You have already uploaded details for this flat ";
                Notification::make()
                        ->title("You have already uploaded details for this flat $flatId for the month $this->month ")
                        ->danger()
                        ->send();
                return 'error';
            }
            Log::info($row);
            $data = CoolingAccount::firstOrCreate([
                'building_id'           => $this->buildingId,
                'flat_id'               => $row['unit_no'],
                'date'                  => $date,
                    ],
                [
                'opening_balance'       => $row['opening_balance_receivable_advance'],
                'consumption'           => $row['in_unit_consumption'],
                'demand_charge'         => $row['in_unit_demand_charge'],
                'security_deposit'      => $row['in_unit_security_deposit'],
                'billing_charges'       => $row['in_unit_billing_charges'],
                'other_charges'         => $row['in_unit_other_charges'],
                'receipts'              => $row['receipts'],
                'closing_balance'       => $row['closing_balance'],
            ]);
        }
        Notification::make()
                        ->title("Details uploaded successfully")
                        ->success()
                        ->send();
        return 'success';
    }
}
