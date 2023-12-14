<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Accounting\OAMInvoice;
use App\Models\Accounting\OAMReceipts;
use App\Models\ApartmentOwner;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\FlatOwners;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DelinquentController extends Controller
{
    function getQuarterPeriod($year, $quarterNumber)
    {
        $startMonth = ($quarterNumber - 1) * 3 + 1;
        $endMonth = $startMonth + 2;

        $start = Carbon::createFromDate($year, $startMonth, 1)->startOfMonth();
        $end = Carbon::createFromDate($year, $endMonth, 1)->endOfMonth();

        return $start->format('d-M-Y') . ' To ' . $end->format('d-M-Y');
    }
    public function getDelinquentOwners(Request $request)
{
    $year = $request->input('year');
    $buildingId = $request->input('building_id');

    $query = Building::where('owner_association_id', auth()->user()->owner_association_id)
    ->when($buildingId, function ($query) use ($buildingId) {
        return $query->where('id', $buildingId);
    });
    
    $buildings = $query->pluck('id');
    
    //Get current date
    $currentDate = Carbon::now();
    
    //Get current year
    $currentYear = $year ?? Carbon::now()->year;
    // dd($currentYear);


    $flats = Flat::whereIn('building_id', $buildings)->with('oaminvoices')->whereExists(function ($query) use ($currentYear, $currentDate, $buildings) {
        $query->select(DB::raw(1))
              ->from('oam_invoices')
              ->whereColumn('oam_invoices.flat_id', 'flats.id')
              ->where('oam_invoices.invoice_period', 'like', '%' . $currentYear . '%')
              ->where('oam_invoices.invoice_date', '<', $currentDate)
              ->whereIn('oam_invoices.building_id', $buildings)
              ->havingRaw('SUM(oam_invoices.invoice_amount) - COALESCE((SELECT SUM(receipt_amount) FROM oam_receipts WHERE oam_receipts.flat_id = flats.id AND oam_receipts.receipt_period LIKE ?), 0) > 0', ["%$currentYear%"]);
    })
    ->paginate(10);
    
    // dd($flats);
    foreach($flats as $flat){
        // dd($filteredFlats);
        $ownerId = FlatOwners::where('flat_id', $flat->id)->where('active', 1)->first();
        $owner = ApartmentOwner::where('id', $ownerId->owner_id)->first();
        $flat['owner'] = $owner;
        // dd($flat);

        $lastReceipt = OAMReceipts::where(['flat_id' => $flat->id])
        ->latest('receipt_date')
        ->first(['receipt_date', 'receipt_amount']);
        $flat['lastReceipt'] = $lastReceipt;

        $yearlyInvoices = OAMInvoice::query()->where('invoice_period', 'like', '%' . $currentYear . '%')
                                                    ->where('flat_id' , $flat->id)
                                                    ->where('invoice_date', '<', $currentDate)
                                                    ->whereIn('building_id', $buildings)->sum('invoice_amount');
            
        $yearlyReceipts = OAMReceipts::where('flat_id' , $flat->id)->where('receipt_period', 'like', '%' . $currentYear . '%')
                                        ->whereIn('building_id', $buildings)->sum('receipt_amount');
        $flat['balance'] = round($yearlyInvoices - $yearlyReceipts,2);

        for ($quarter = 1; $quarter <= 4; $quarter++) {
            // $flat->id= 2;
            $quarterPeriod = $this->getQuarterPeriod($currentYear, $quarter);
            // dd($quarterPeriod);

            // Fetch invoices for the quarter
            $receipts = OAMReceipts::where('flat_id', $flat->id)
                ->where('receipt_period', $quarterPeriod)
                ->first()?->receipt_amount;

            $invoicesss = OAMInvoice::where('flat_id', $flat->id)
                ->where('invoice_period', $quarterPeriod)
                ->first()?->invoice_amount;
            $dueAmount = abs($receipts - $invoicesss);
            // dd($invoicesss-$receipts);
            $flat["Q{$quarter}_receipts"] = round($dueAmount, 2);

        }

        $lastInvoice = OAMInvoice::where(['flat_id' => $flat->id])
                            ->latest('invoice_date')
                            ->first()?->invoice_pdf_link;
        $flat['invoice_file'] = $lastInvoice;
    }

    // Return the data, either as JSON or as a rendered Blade view
    return view('partials.delinquent-rows', ['flats' => $flats]);
}
}
