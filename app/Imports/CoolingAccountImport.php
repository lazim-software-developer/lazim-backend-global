<?php

namespace App\Imports;

use Illuminate\Support\Str;
use App\Models\Building\Flat;
use App\Models\CoolingAccount;
use Filament\Facades\Filament;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CoolingAccountImport implements ToCollection, WithHeadingRow
{
    // protected $budgetPeriod;
    protected $buildingId;
    protected $month;
    protected $dueDate;

    public function __construct($buildingId, $month, $dueDate)
    {

        $this->buildingId = $buildingId;
        $this->month = $month;
        $this->dueDate = $dueDate;
    }
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        $date = Carbon::parse($this->month)->format('Y-m-d');
        // Define the expected headings
        $expectedHeadings = [
            'unit_no','opening_balance_receivable_advance',
            'in_unit_consumption', 'in_unit_demand_charge',
            'in_unit_security_deposit', 'in_unit_billing_charges',
            'in_unit_other_charges', 'receipts', 'closing_balance', 'status'
           ];

           if($rows->first()== null){
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("You have uploaded an empty file")
                ->send();
            return 'failure';
            }

           // Extract the headings from the first row
           $extractedHeadings = array_keys($rows->first()->toArray());

           // Check if all expected headings are present in the extracted headings
           $missingHeadings = array_diff($expectedHeadings, $extractedHeadings);

           if (!empty($missingHeadings)) {
               Notification::make()
                   ->title("Upload valid excel file.")
                   ->danger()
                   ->body("Missing headings: " . implode(', ', $missingHeadings))
                   ->send();
               return 'failure';
           } else {
            $tenant = Filament::getTenant();

            foreach ($rows as $row) {
                $flatId = Flat::where(['building_id' => $this->buildingId, 'property_number' => $row['unit_no']])->first()?->id;
                $status = $row['status'] ?? 'pending';
                if(! $flatId || ! in_array($status,['pending','overdue','paid'])) {
                    continue;
                }
                if (CoolingAccount::where(['building_id' => $this->buildingId, 'flat_id' => $flatId])->exists()) {
                    Notification::make()
                        ->title("You have already uploaded details for the month ".Str::ucfirst($this->month))
                        ->danger()
                        ->send();
                    return 'error';
                }
                CoolingAccount::firstOrCreate(
                    [
                        'building_id'           => $this->buildingId,
                        'flat_id'               => $flatId,
                        'date'                  => $date,
                        'owner_association_id'  => $tenant?->id ?? auth()->user()->owner_association_id,
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
                        'status'                => $status,
                        'due_date'              => $this->dueDate? : null
                    ]
                );
            }
            Notification::make()
                ->title("Details uploaded successfully")
                ->success()
                ->send();
            return 'success';
        }
    }
}
