<?php

namespace App\Imports;

use App\Models\GeneralFund;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ReserveFundStatementImport implements ToCollection,WithHeadingRow
{
    public function __construct(protected $buildingId,protected $date)
    {

    }
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $expectedHeadings = [
            'date', 'description',
           'debit_amount',
           'credit_amount',
           ];
   
           // Extract the headings from the first row
           $extractedHeadings = array_keys($collection->first()->toArray());
   
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
        foreach($collection as $row){

                if($row["debit_amount"] > 0 || $row["credit_amount"] > 0){
                    $date = Carbon::createFromFormat('j/n/Y',$row['date'])->format('Y-m-d');
                    GeneralFund::create([
                        "statement_date" => $this->date,
                        "building_id" => $this->buildingId,
                        "date" => $date,
                        "description" => $row["description"],
                        "debited_amount" => $row["debit_amount"],
                        "credited_amount" => $row["credit_amount"],
                        "type" => "Reserve Fund"
                    ]);
                }
            }
        }
        Notification::make()
            ->title("Details uploaded successfully")
            ->success()
            ->send();
        return 'success';
    }
}
