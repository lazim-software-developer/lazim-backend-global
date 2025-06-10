<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use Stripe\Stripe;
use App\Models\Order;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;
use App\Models\Building\Flat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\Accounting\OAMInvoice;
use App\Models\Accounting\OAMReceipts;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ServiceChargeResource;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentController extends Controller
{
    public function fetchServiceCharges(Request $request, Flat $flat)
    {
        // $perPage = 10;

        // // Get all invoices for the given flat
        // $invoices = OAMInvoice::where('flat_id', $flat->id)->get();

        // // Aggregate receipt amounts per receipt period
        // $receiptAmounts = OAMReceipts::where('flat_id', $flat->id)
        //     ->get()
        //     ->mapToGroups(function ($receipt) {
        //         $period = $this->parseDateRange($receipt->receipt_period);
        //         return [$receipt->receipt_period => $receipt->receipt_amount];
        //     })
        //     ->map(function ($amounts) {
        //         return $amounts->sum();
        //     });

        // // Filter out unpaid invoices
        // $unpaidInvoices = $invoices->filter(function ($invoice) use ($receiptAmounts) {
        //     $invoicePeriod = $this->parseDateRange($invoice->invoice_period);
        //     // $invoiceQuarter = 'Q' . getQuarter($invoicePeriod['start']);

        //     // Sum of receipts within the invoice period
        //     $totalReceiptAmount = 0;
        //     foreach ($receiptAmounts as $period => $amount) {
        //         $receiptPeriod = $this->parseDateRange($period);
        //         if (
        //             $invoicePeriod['start']->between($receiptPeriod['start'], $receiptPeriod['end']) ||
        //             $invoicePeriod['end']->between($receiptPeriod['start'], $receiptPeriod['end'])
        //         ) {
        //             $totalReceiptAmount += $amount;
        //         }
        //     }

        //     // Consider unpaid if total receipt amount is less than invoice amount
        //     if ($totalReceiptAmount < $invoice->invoice_amount) {
        //         return [
        //             'invoice_pdf_link' => $invoice->document,
        //             'invoice_quarter' => $invoice->invoice_quarter
        //         ];
        //     }

        //     return null;
        // });

        // $currentPageItems =
        //     $unpaidInvoices->slice(($request->input('page', 1) - 1) * $perPage, $perPage)->values()->all();

        // // Create our paginator and pass it to the view
        // $paginatedItems = new LengthAwarePaginator($currentPageItems, count($unpaidInvoices), $perPage);

        // // Set url path for generated links
        // $paginatedItems->setPath($request->url());

        // return response()->json($paginatedItems);

        $invoice = OAMInvoice::where('flat_id',$flat->id)->latest('invoice_date')->first();
        if ($invoice){
            return ['data' => [new ServiceChargeResource($invoice)]];
        }
        return ['data' => []];
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

    /**
     * Fetch or retrieve the service charge PDF for an invoice.
     *
     * @param OAMInvoice $invoice
     * @return array{file_path: string, url: string}
     * @throws Exception
     */
    public function fetchServiceChargePDF(OAMInvoice $invoice)
    {
        // Check if pdf_path exists and file is present in S3
        if (!empty($invoice->pdf_path) && Storage::disk('s3')->exists($invoice->pdf_path)) {
            return [
                'file_path' => $invoice->pdf_path,
                'url' => Storage::disk('s3')->url($invoice->pdf_path),
            ];
        }
        // Fetch PDF from API
        $pdfLink = $invoice->invoice_detail_link;
        try {
            $response = Http::withoutVerifying()->withHeaders([
                'content-type' => 'application/json',
                'consumer-id' => env('MOLLAK_CONSUMER_ID'),
            ])->get($pdfLink);

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch PDF from API. Status: {$response->status()}");
            }

            // Generate filename and store in S3
            $fileName = 'invoices/invoice_' . $invoice->id . '_' . time() . '.pdf';
            $fullPath = 'dev/' . $fileName;

            // Store PDF in S3 with public visibility
            Storage::disk('s3')->put($fullPath, $response->body(), 'public');

            // Verify the file was stored
            if (!Storage::disk('s3')->exists($fullPath)) {
                throw new \Exception('Failed to store PDF in S3');
            }

            // Update database with the file path
            $invoice->update(['pdf_path' => $fullPath]);

            // Generate public S3 URL
            $url = Storage::disk('s3')->url($fullPath);

            return [
                'file_path' => $fullPath,
                'url' => $url,
            ];
        } catch (\Exception $e) {
            Log::error('##### PaymentController -> fetchServiceChargePDF ##### Error fetching/storing PDF', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'pdf_link' => $pdfLink,
            ]);
            throw new \Exception('Unable to fetch or store PDF: ' . $e->getMessage());
        }

    }

    public function createPaymentIntent()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => 1000,
                'currency' => 'aed',
            ]);

            return response()->json(['clientSecret' => $paymentIntent->client_secret]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Fetch payment status
    public function fetchPaymentStatus(Order $order, Request $request)
    {
        if ($request->has('building_id')) {
            DB::table('building_owner_association')
                ->where(['building_id' => $request->building_id, 'active' => true])->first()->owner_association_id;
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $paymentIntentId = $order->payment_intent_id;
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            $order->update([
                'payment_status' =>  $paymentIntent->status
            ]);

            return response()->json([
                'status' => $paymentIntent->status,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function fecthInvoiceDetails(Flat $flat)
    {
        try {
            $invoice =  OAMInvoice::where('flat_id', operator: $flat->id)->latest('invoice_date')->first();
            if (!$invoice) {
                return ["data" =>['outstanding_balance' => 0,'virtual_account_number' => 0]];
            }
            $receipts = OAMReceipts::where('flat_id', $flat->id)
            ->where('receipt_date', '>=', $invoice->invoice_date)
            ->get();
            $balance = $invoice->due_amount;
            if ($receipts->isNotEmpty()) {
                $balance = $invoice->due_amount - $receipts->sum('receipt_amount');
            }
            return [
                "data" =>[
                    'outstanding_balance' => $balance,
                    'virtual_account_number' => $flat->virtual_account_number,]
            ];
        } catch (\Exception $e) {
            Log::error('##### PaymentController -> fecthInvoiceDetails #####', [
                'flat_id' => $flat->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
