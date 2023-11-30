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

        // List all invoices for the last quarter
        $currentDate = Carbon::now();

        // Get the first day of the last quarter
        $lastQuarterStart = $currentDate->subQuarter()->startOfQuarter();

        // Get the last day of the last quarter
        $lastQuarterEnd = $lastQuarterStart->copy()->endOfQuarter();

        // Fetch all last 4 invocies
        $currentYear = Carbon::now()->year;

        // End 

        // Format dates
        $lastQuarterStartFormatted = $lastQuarterStart->format('d-M-Y');
        $lastQuarterEndFormatted = $lastQuarterEnd->format('d-M-Y');

        // Combine the dates into the desired format
        // $lastQuarterPeriod = $lastQuarterStartFormatted . ' To ' . $lastQuarterEndFormatted;

        $lastQuarterPeriod = "01-Jan-2023 To 31-Mar-2023";

        Log::info("HERREE", [$lastQuarterPeriod]);

        // Fetch invoices for last quarter
        $lastQuarterInvoices = OAMInvoice::query()
            ->where('invoice_period', $lastQuarterPeriod)
            ->where('invoice_date', '<', $currentDate)
            ->whereIn('building_id', $buildings)
            ->paginate(10);

        foreach ($lastQuarterInvoices as $invoice) {
            $receipts = OAMReceipts::where(['flat_id' => $invoice->flat_id, 'receipt_period' => $lastQuarterPeriod])->get();

            // Get all receipts
            $invoice['receipts'] = $receipts;

            // GEt last receipt for this flat
            $lastReceipt = OAMReceipts::where(['flat_id' => $invoice->flat_id, 'receipt_period' => $lastQuarterPeriod])
                ->latest('receipt_date')
                ->first(['receipt_date', 'receipt_amount']);

            // Fetch q1,q2 q3 and q4 invoice_amount and invocie_pdf_link for the flat_id from oam_invocies table

            // Fetch owners for the flats
            $ownerId = FlatOwners::where('flat_id', $invoice->flat_id)->where('active', 1)->first();
            $flat = Flat::where('id', $invoice->flat_id)->first();

            $owner = ApartmentOwner::where('id', $ownerId->id)->first();

            $invoice['owner'] = $owner;
            $invoice['flat'] = $flat;

            $invoice['lastReceipt'] = $lastReceipt;


            for ($quarter = 1; $quarter <= 4; $quarter++) {
                $quarterPeriod = $this->getQuarterPeriod($currentYear, $quarter);

                // Fetch invoices for the quarter
                $invoicesss = OAMInvoice::where('flat_id', $invoice->flat_id)
                    ->where('invoice_period', $quarterPeriod)
                    ->first(['invoice_amount', 'invoice_pdf_link']);

                $invoice["Q{$quarter}_invoices"] = $invoicesss;

                $invoice['lastReceipt'] = $lastReceipt;
            }
        }

        return [
            'data' => $lastQuarterInvoices
        ];
    }
}
