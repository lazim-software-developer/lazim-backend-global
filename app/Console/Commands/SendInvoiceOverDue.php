<?php

namespace App\Console\Commands;

use App\Models\User\User;
use App\Models\LegalNotice;
use App\Models\GlobalSetting;
use Filament\Facades\Filament;
use Illuminate\Console\Command;
use App\Models\OwnerAssociation;
use App\Jobs\InvoiceNotification;
use App\Jobs\UpdateLegalNoticeJob;
use App\Models\AccountCredentials;
use Illuminate\Support\Facades\DB;
use App\Models\Building\FlatTenant;
use Illuminate\Support\Facades\Log;
use App\Models\Accounting\OAMInvoice;
use App\Models\Accounting\OAMReceipts;
use App\Models\InvoiceReminderTracking;

class SendInvoiceOverDue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:sent-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
#TODO Need to re-evaluate compl
        $globalSetting = GlobalSetting::first()->toArray();
        // Get the invoices that are overdue
        // $invoicesData = OAMInvoice::with('flat:id,property_number,mollak_property_id,building_id','building:id,name')
        // ->select('id','building_id','flat_id','invoice_due_date','invoice_number','due_amount','invoice_pdf_link','invoice_date')
        // ->where('invoice_date', '<', date('Y-01-01'))
        // ->where('invoice_due_date', '<', now())
        // ->get();
        $invoicesData = OAMInvoice::with('flat:id,property_number,mollak_property_id,building_id', 'building:id,name')
        ->select('id', 'building_id', 'flat_id', 'invoice_due_date', 'invoice_number', 'due_amount', 'invoice_pdf_link', 'invoice_date')
        ->whereIn('id', function ($query) {
            $query->select(DB::raw('MAX(id)'))
                ->from('oam_invoices')
                ->where('invoice_date', '<', date('Y-01-01'))
                ->where('invoice_due_date', '<', now())
                ->groupBy('flat_id');
        })
        ->get();
        // dd($invoicesData);
        $invoices = $invoicesData->toArray();
        // Send notifications for overdue invoices
        foreach ($invoices as $invoice) {
            $totalPaid = OAMReceipts::where('building_id', $invoice['building_id'])
                ->where('flat_id', $invoice['flat_id'])
                ->where('receipt_date', '>=', $invoice['invoice_date'])
                ->sum('receipt_amount');
            $remainingAmount = $invoice['due_amount'] - $totalPaid;
            if($remainingAmount > 0){
                $owner = FlatTenant::where('flat_id', $invoice['flat_id'])->where('building_id', $invoice['building_id'])
                ->where('role', 'Owner')
                ->first();

                $owner = $owner ? $owner->toArray() : null;
                if ($owner) {
                    try {
                        $credentials = AccountCredentials::where('oa_id', $owner['owner_association_id'])->where('active', true)->latest()->first();
                        $mailCredentials = [
                           //  'mail_host' => $credentials->host ?? env('MAIL_HOST'),
                          //  'mail_port' => $credentials->port ?? env('MAIL_PORT'),
                         //   'mail_username' => $credentials->username ?? env('MAIL_USERNAME'),
                        //    'mail_password' => $credentials->password ?? env('MAIL_PASSWORD'),
                       //     'mail_encryption' => $credentials->encryption ?? env('MAIL_ENCRYPTION'),
                      //     'mail_from_address' => $credentials->email ?? env('MAIL_FROM_ADDRESS'),
                        ];
                        $ownerAssociation=OwnerAssociation::where('id',$owner['owner_association_id'])->value('name');
                        $OaName = $ownerAssociation ?: 'Admin';
                        $flat = $invoice['flat'] ? $invoice['flat'] : null;
                        $building = $invoice['building'] ? $invoice['building'] : null;
                        $UserDetail=User::where('id',$owner['tenant_id'])->first()->toArray();
                        $PaymentDate=\Carbon\Carbon::parse($invoice['invoice_date'])->addDays($globalSetting['payment_day'])->format('Y-m-d');
                        $FollowUpDate=\Carbon\Carbon::parse($invoice['invoice_date'])->addDays($globalSetting['follow_up_day'])->format('Y-m-d');
                        $InvoiceData=[
                            'invoice_id'=>$invoice['id'],
                            'user_id'=>$UserDetail['id'],
                            'invoice_number'=>$invoice['invoice_number'],
                            'invoice_amount'=>$remainingAmount,
                            'invoice_actual_date'=>$invoice['invoice_date'],
                            'user_email'=>$UserDetail['email'],
                            'building_id'=>$invoice['building_id'],
                            'flat_id'=>$invoice['flat_id'],
                            'created_at'=>date('Y-m-d'),
                            'updated_at'=>date('Y-m-d')
                        ];

                        // if($PaymentDate==date('Y-m-d')){
                        //     InvoiceReminderTracking::create($InvoiceData);
                        //     InvoiceNotification::dispatch($UserDetail['email'], $UserDetail['first_name'],$invoice['invoice_number'], $invoice['invoice_date'], $invoice['invoice_due_date'], $remainingAmount, $mailCredentials, $OaName);
                        //     echo "Invoice overdue notification sent for invoice ID: " . $invoice['invoice_number'] . "\n";
                        // }
                        // if($FollowUpDate==date('Y-m-d')){
                        //     InvoiceReminderTracking::create($InvoiceData);
                        //     InvoiceNotification::dispatch($UserDetail['email'], $UserDetail['first_name'],$invoice['invoice_number'], $invoice['invoice_date'], $invoice['invoice_due_date'], $remainingAmount, $mailCredentials, $OaName);
                        //     echo "Invoice overdue notification sent for invoice ID: " . $invoice['invoice_number'] . "\n";
                        // }
                        if($PaymentDate==date('Y-m-d') || $FollowUpDate==date('Y-m-d')){
                      //      InvoiceReminderTracking::create($InvoiceData);
                      //      InvoiceNotification::dispatch($UserDetail['email'], $UserDetail['first_name'],$invoice['invoice_number'], $invoice['invoice_date'], $invoice['invoice_due_date'], $remainingAmount, $mailCredentials, $OaName,$flat, $building);
                            echo "Invoice overdue notification sent for invoice ID: " . $invoice['invoice_number'] . "\n";
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to send overdue receipt notification: ' . $e->getMessage());
                    }
                }
                // \Log::info('Invoice overdue notification sent for invoice ID: ' . $invoice['invoice_number']);
            }
        }
    }
}
