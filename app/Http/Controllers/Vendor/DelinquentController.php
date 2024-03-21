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
use Illuminate\Pagination\LengthAwarePaginator;
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

    function checkDueDate($flat,$year)
    {
        
        $quarters = ["01-Jan-$year To 31-Mar-$year","01-Apr-$year To 30-Jun-$year","01-Jul-$year To 30-Sep-$year","01-Oct-$year To 31-Dec-$year"];
        foreach($quarters as $quarter){
            $invoiceDate =OAMInvoice::where(['flat_id' => $flat->id, 'invoice_period' => $quarter])->first()?->invoice_due_date;
            $receiptDate =OAMReceipts::where(['flat_id' => $flat->id, 'receipt_period' => $quarter])->first()?->receipt_date;
            if ($invoiceDate && $receiptDate && Carbon::parse($receiptDate)->greaterThan(Carbon::parse($invoiceDate))) {
                return true;
            }
        }
        return false;
    }

    protected function filterFlats($flats, $currentYear, $currentDate, $buildings)
    {
        return $flats->filter(function ($flat) use ($currentYear, $currentDate, $buildings) {
            $yearlyInvoices = OAMInvoice::query()->where('invoice_period', 'like', '%' . $currentYear . '%')
                                                            ->where('flat_id' , $flat->id)
                                                            ->where('invoice_date', '<', $currentDate)
                                                            ->whereIn('building_id', $buildings)->sum('invoice_amount');
                    
                    $yearlyReceipts = OAMReceipts::where('flat_id' , $flat->id)->where('receipt_period', 'like', '%' . $currentYear . '%')->whereIn('building_id', $buildings)->sum('receipt_amount');
                    
                    if ((int)$yearlyInvoices - (int)$yearlyReceipts >0 || $this->checkDueDate($flat,$currentYear)) {
                        
                        return $flat;
                    } else {
                        Log::info('invoice'. (int)$yearlyInvoices);
                        Log::info('recipts'.(int)$yearlyReceipts);
                    }
        });
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
        
        $currentDate = Carbon::now();
        $currentYear = $currentDate->year;

        $flats = Flat::whereIn('building_id', $buildings)->with('oaminvoices')->get();
        $filteredFlats = $this->filterFlats($flats, $currentYear, $currentDate, $buildings);

        foreach ($filteredFlats as $flat) {
            // dd($filteredFlats);
            $ownerId = FlatOwners::where('flat_id', $flat->id)->where('active', 1)->first();
            $owner = ApartmentOwner::where('id', $ownerId->owner_id)->first();
            $flat['owner'] = $owner;
            // dd($flat);

            $lastReceipt = OAMReceipts::where(['flat_id' => $flat->id])
            ->latest('receipt_date')
            ->first(['receipt_date', 'receipt_amount']);
            $flat['lastReceipt'] = $lastReceipt;
            $lastInvoice = OAMInvoice::where(['flat_id' => $flat->id])
                                ->latest('invoice_date')
                                ->first();
            $flat['balance'] = $lastInvoice->due_amount;
            if($lastInvoice?->invoice_due_date && $lastReceipt?->receipt_date && Carbon::parse($lastReceipt?->receipt_date)->greaterThan(Carbon::parse($lastInvoice?->invoice_due_date)))
            {
                $dueAmount = $lastInvoice->due_amount - $lastReceipt?->receipt_amount;
                $flat['balance'] = $dueAmount;
            }

            for ($quarter = 1; $quarter <= 4; $quarter++) {

                $quarterPeriod = $this->getQuarterPeriod($currentYear, $quarter);
                // dd($quarterPeriod);
    
                // Fetch invoices for the quarter
                $receipts = OAMReceipts::where('flat_id', $flat->id)
                    ->where('receipt_period', $quarterPeriod)
                    ->first()?->receipt_amount;

                $invoicesss = OAMInvoice::where('flat_id', $flat->id)
                    ->where('invoice_period', $quarterPeriod)
                    ->first()?->invoice_amount;
                $dueAmount = $invoicesss - $receipts;
                
                $flat["Q{$quarter}_receipts"] = round($dueAmount, 2);
    
            }
            $flat['invoice_file'] = $lastInvoice?->invoice_pdf_link;
        }
        $paginatedItems = $this->paginateFlats($filteredFlats);

        // Return the data, either as JSON or as a rendered Blade view
        return view('partials.delinquent-rows', ['flats' => $paginatedItems]);
    }

    protected function paginateFlats($flats)
    {
        $perPage = 10;
        $currentPage = request()->input('page', 1);
        $currentItems = $flats->slice(($currentPage - 1) * $perPage, $perPage);

        return new LengthAwarePaginator(
            $currentItems,
            $flats->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query()
            ]
        );
    }
}
