<?php

namespace App\Imports;

use App\Models\GeneralFund;
use Carbon\Carbon;
use DateInterval;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GeneralFundImport implements ToCollection,WithHeadingRow
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

            if($row["debit_amount"] > 0 || $row["credit_amount"] > 0){
                $date = Carbon::createFromFormat('d/m/Y',$row['date'])->format('Y-m-d');
                GeneralFund::create([
                    "statement_date" => $this->date,
                    "building_id" => $this->buildingId,
                    "date" => $date,
                    "description" => $row["description"],
                    "debited_amount" => $row["debit_amount"],
                    "credited_amount" => $row["credit_amount"],
                    "type" => "General Fund"
                ]);
            }
        }
    }
}
