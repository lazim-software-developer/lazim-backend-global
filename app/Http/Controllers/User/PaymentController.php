<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Accounting\OAMInvoice;
use App\Models\Accounting\OAMReceipts;
use App\Models\Building\Flat;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function fetchServiceCharges(Request $request, Flat $flat)
    {
        $perPage = 10;

        // Get all invoices for the given flat
        $invoices = OAMInvoice::where('flat_id', $flat->id)->get();

        // Aggregate receipt amounts per receipt period
        $receiptAmounts = OAMReceipts::where('flat_id', $flat->id)
            ->get()
            ->mapToGroups(function ($receipt) {
                $period = $this->parseDateRange($receipt->receipt_period);
                return [$receipt->receipt_period => $receipt->receipt_amount];
            })
            ->map(function ($amounts) {
                return $amounts->sum();
            });

        // Filter out unpaid invoices
        $unpaidInvoices = $invoices->filter(function ($invoice) use ($receiptAmounts) {
            $invoicePeriod = $this->parseDateRange($invoice->invoice_period);
            // $invoiceQuarter = 'Q' . getQuarter($invoicePeriod['start']);

            // Sum of receipts within the invoice period
            $totalReceiptAmount = 0;
            foreach ($receiptAmounts as $period => $amount) {
                $receiptPeriod = $this->parseDateRange($period);
                if (
                    $invoicePeriod['start']->between($receiptPeriod['start'], $receiptPeriod['end']) ||
                    $invoicePeriod['end']->between($receiptPeriod['start'], $receiptPeriod['end'])
                ) {
                    $totalReceiptAmount += $amount;
                }
            }

            // Consider unpaid if total receipt amount is less than invoice amount
            if ($totalReceiptAmount < $invoice->invoice_amount) {
                return [
                    'invoice_pdf_link' => $invoice->document,
                    'invoice_quarter' => $invoice->invoice_quarter
                ];
            }

            return null;
        });

        $currentPageItems =
            $unpaidInvoices->slice(($request->input('page', 1) - 1) * $perPage, $perPage)->values()->all();

        // Create our paginator and pass it to the view
        $paginatedItems = new LengthAwarePaginator($currentPageItems, count($unpaidInvoices), $perPage);

        // Set url path for generated links
        $paginatedItems->setPath($request->url());

        return response()->json($paginatedItems);
    }

    public static function parseDateRange($dateRange)
    {
        [$startDate, $endDate] = explode(' To ', $dateRange);
        return [
            'start' => \Carbon\Carbon::createFromFormat('d-M-Y', trim($startDate)),
            'end' => \Carbon\Carbon::createFromFormat('d-M-Y', trim($endDate))
        ];
    }

    public function fetchPDF(OAMInvoice $invoice)
    {
        $invoice->invoice_pdf_link;

        return $response = Http::withoutVerifying()->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get("https://b2bgateway.dubailand.gov.ae/mollak/external/sync/invoices/235553/17651639/0223010004632489/detail");
    }

    public function fetchPDF2(OAMInvoice $invoice)
    {
        $invoice->invoice_pdf_link;

        return $response = Http::withoutVerifying()->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get("https://b2bgateway.dubailand.gov.ae/mollak/external/sync/invoices/235553/17651639/0223010004632489/pdf");
    }
}
