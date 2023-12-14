<?php

namespace App\Filament\Pages;

use App\Models\Accounting\Invoice;
use App\Models\Accounting\OAMInvoice;
use App\Models\Accounting\OAMReceipts;
use App\Models\ApartmentOwner;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\FlatOwners;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;
use \Filament\Pages\Actions;
use Illuminate\Support\Facades\DB;
use \Livewire\WithPagination;

class DelinquentOwners extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.delinquent-owners';

    protected static ?string $slug = 'delinquent-owners';

    


    function getQuarterPeriod($year, $quarterNumber)
    {
        $startMonth = ($quarterNumber - 1) * 3 + 1;
        $endMonth = $startMonth + 2;

        $start = Carbon::createFromDate($year, $startMonth, 1)->startOfMonth();
        $end = Carbon::createFromDate($year, $endMonth, 1)->endOfMonth();

        return $start->format('d-M-Y') . ' To ' . $end->format('d-M-Y');
    }

    protected function getViewData(): array
    {
        // Fetch all buildigns for logged in users
        $buildings = Building::where('owner_association_id', auth()->user()->owner_association_id)->pluck('id');

        //Get current date
        $currentDate = Carbon::now();

        //Get current year
        $currentYear = Carbon::now()->year;

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
                Log::info('receipt'.$receipts);
                Log::info('invoice'.$invoicesss);
                $flat["Q{$quarter}_receipts"] = round($dueAmount, 2);
    
            }
            $lastInvoice = OAMInvoice::where(['flat_id' => $flat->id])
                                ->latest('invoice_date')
                                ->first()?->invoice_pdf_link;
            $flat['invoice_file'] = $lastInvoice;
        }

        return [
                'data' => $flats,
                'years' => range($currentYear,Carbon::now()->subYears(5)->year ), // Adjust range as needed
                'buildings' => Building::where('owner_association_id', auth()->user()->owner_association_id)->get() // Fetch all buildings for the dropdown
        
            ];
    }
}
