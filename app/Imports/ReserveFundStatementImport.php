<?php

namespace App\Imports;

use App\Models\GeneralFund;
use Carbon\Carbon;
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
        foreach($collection as $row){

                // dd($date = Carbon::createFromFormat('j/n/Y',$row['date'])->format('Y-m-d'));
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
}
